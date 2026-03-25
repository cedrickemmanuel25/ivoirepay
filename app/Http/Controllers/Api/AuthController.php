<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\AfricasTalkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AfricasTalkingService $smsService
    ) {}

    // ─── Step 1: Send OTP ────────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        \Log::info("sendOtp hit: " . json_encode($request->all()));
        $request->validate([
            'phone' => [
                'required', 
                'string', 
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^(\+225|225)?[0-9]{10}$/', $value)) {
                        $fail('Le format du numéro de téléphone est invalide.');
                    }
                }
            ],
            'type'  => 'required|in:registration,login,reset',
        ]);

        $phone = $this->normalizePhone($request->phone);

        // Invalidate previous codes for this phone & type
        OtpCode::where('phone', $phone)
            ->where('type', $request->type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'phone'      => $phone,
            'code'       => $code,
            'type'       => $request->type,
            'expires_at' => now()->addMinutes((int) env('OTP_VALIDITY_MINUTES', 5)),
            'attempts'   => 0,
        ]);

        \Log::info("Envoi OTP : phone={$phone}, code={$code}");
        $sent = $this->smsService->sendOtp($phone, $code);
        \Log::info("Résultat envoi OTP : success=" . ($sent ? 'oui' : 'non'));

        return response()->json([
            'message' => 'Code OTP envoyé avec succès.',
            'phone'   => $phone,
        ]);
    }

    // ─── Step 2: Verify OTP ──────────────────────────────────────────────────

    public function verifyOtp(Request $request)
    {
        \Log::info("verifyOtp hit: " . json_encode($request->all()));

        // Backward compatibility: support 'otp_code' if 'code' is missing
        if (!$request->has('code') && $request->has('otp_code')) {
            $request->merge(['code' => $request->otp_code]);
        }

        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string|size:6',
            'type'  => 'required|in:registration,login,reset',
        ]);

        $phone   = $this->normalizePhone($request->phone);
        $maxAttempts = (int) env('OTP_MAX_ATTEMPTS', 3);

        $otp = OtpCode::where('phone', $phone)
            ->where('type', $request->type)
            ->whereNull('used_at')
            ->latest()
            ->first();

        \Log::info("OTP lookup result for phone={$phone}, type={$request->type}: " . ($otp ? "found (code={$otp->code})" : "not found"));

        if (! $otp) {
            return response()->json(['message' => 'Code OTP introuvable ou déjà utilisé.'], 422);
        }

        if ($otp->expires_at < now()) {
            return response()->json(['message' => 'Code OTP expiré.'], 422);
        }

        if ($otp->attempts >= $maxAttempts) {
            return response()->json(['message' => 'Nombre maximum de tentatives atteint.'], 429);
        }

        if ($otp->code !== $request->code) {
            $otp->increment('attempts');
            $remaining = $maxAttempts - $otp->attempts;
            return response()->json([
                'message'   => 'Code OTP incorrect.',
                'remaining' => $remaining,
            ], 422);
        }

        // Mark OTP as used
        $otp->update(['used_at' => now()]);

        // Check if user exists for navigation logic
        $userExists = User::where('phone', $phone)->exists();

        // Generate a short-lived temporary token stored in cache
        $tempToken = \Illuminate\Support\Str::random(40);
        \Illuminate\Support\Facades\Cache::put("otp_verified:{$phone}", [
            'phone' => $phone,
            'type'  => $request->type,
        ], now()->addMinutes(10));

        $user = User::where('phone', $phone)->first();
        
        // If user already exists, we might want to give them a real token 
        // IF they haven't set a PIN yet (merchants in flow).
        $token = $tempToken;
        if ($user && !$user->has_pin) {
            $user->tokens()->delete();
            $token = $user->createToken('mobile')->plainTextToken;
        }

        return response()->json([
            'message'     => 'OTP vérifié.',
            'token'       => $token,
            'phone'       => $phone,
            'is_new_user' => !$userExists,
            'user'        => $user ? collect($user->toArray())->only(['id', 'name', 'phone', 'role', 'avatar', 'kyc_status', 'has_pin'])->all() : null,
        ]);
    }

    // ─── Step 3: Register ─────────────────────────────────────────────────────

    public function register(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name'  => 'required|string|min:2|max:100',
            'role'  => 'required|in:client,merchant',
            'pin'   => 'nullable|digits_between:4,6',
        ]);

        $phone = $this->normalizePhone($request->phone);

        \Log::info("Starting registration process for phone: $phone");
        
        // Verify the phone was OTP-verified
        \Log::info("Checking recent OTP verification...");
        $recentOtp = OtpCode::where('phone', $phone)
            ->where('type', 'registration')
            ->whereNotNull('used_at')
            ->where('used_at', '>=', now()->subMinutes(10))
            ->exists();

        if (! $recentOtp) {
            \Log::warning("Registration failed: OTP not verified or expired.");
            return response()->json(['message' => 'Vérification OTP requise avant l\'inscription.'], 422);
        }

        \Log::info("Checking if user already exists...");
        if (User::where('phone', $phone)->exists()) {
            \Log::warning("Registration failed: User already exists.");
            return response()->json(['message' => 'Ce numéro est déjà enregistré.'], 422);
        }

        \Log::info("Creating user record...");
        $user = User::create([
            'name'     => $request->name,
            'phone'    => $phone,
            'role'     => $request->role,
            'pin_hash' => $request->pin ? Hash::make($request->pin, ['rounds' => 12]) : null,
            'is_active' => true,
            'phone_verified_at' => now(),
        ]);

        \Log::info("Creating API token...");
        $token = $user->createToken('mobile')->plainTextToken;

        \Log::info("Registration successful for user ID: " . $user->id);
        return response()->json([
            'message' => 'Compte créé avec succès.',
            'token'   => $token,
            'user'    => collect($user->toArray())->only(['id', 'name', 'phone', 'role', 'avatar', 'kyc_status', 'has_pin'])->all(),
        ], 201);
    }

    // ─── Step 4: Login with PIN ───────────────────────────────────────────────

    public function loginWithPin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin'   => 'required|digits_between:4,6',
        ]);

        $phone   = $this->normalizePhone($request->phone);
        
        $user = User::where('phone', $phone)->first();

        $cacheKey = "pin_fails:{$phone}";
        $maxFails = (int) env('PIN_MAX_FAILED_ATTEMPTS', 5);

        if (! $user) {
            return response()->json(['message' => 'Numéro de téléphone introuvable.'], 404);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Compte suspendu. Contactez le support.'], 403);
        }

        if (! Hash::check($request->pin, $user->pin_hash)) {
            $fails = (int) Cache::increment($cacheKey);
            Cache::put($cacheKey, $fails, now()->addHours(1));

            if ($fails >= $maxFails) {
                $user->update(['is_active' => false]);
                return response()->json(['message' => 'Compte suspendu après trop de tentatives.'], 403);
            }

            return response()->json([
                'message'   => 'PIN incorrect.',
                'remaining' => $maxFails - $fails,
            ], 401);
        }

        // Clear fails on success
        Cache::forget($cacheKey);

        // Revoke old tokens and issue fresh one
        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token'   => $token,
            'user'    => collect($user->toArray())->only(['id', 'name', 'phone', 'role', 'avatar', 'kyc_status', 'has_pin'])->all(),
        ]);
    }

    // ─── Setup PIN (first time) ───────────────────────────────────────────────

    public function setupPin(Request $request)
    {
        $request->validate([
            'pin'             => 'required|digits_between:4,6',
            'pin_confirmation' => 'required|same:pin',
        ]);

        $request->user()->update([
            'pin_hash' => Hash::make($request->pin, ['rounds' => 12]),
        ]);

        return response()->json(['message' => 'PIN configuré avec succès.']);
    }

    // ─── Change PIN ───────────────────────────────────────────────────────────

    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin'     => 'required|digits_between:4,6',
            'new_pin'         => 'required|digits_between:4,6|different:current_pin',
            'new_pin_confirmation' => 'required|same:new_pin',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_pin, $user->pin_hash)) {
            return response()->json(['message' => 'PIN actuel incorrect.'], 422);
        }

        $user->update([
            'pin_hash' => Hash::make($request->new_pin, ['rounds' => 12]),
        ]);

        // Revoke all tokens to force re-login
        $user->tokens()->delete();

        return response()->json(['message' => 'PIN modifié avec succès. Veuillez vous reconnecter.']);
    }

    // ─── Update Profile (Client) ─────────────────────────────────────────────

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|min:2|max:100',
            'avatar' => 'nullable|image|max:2048', // max 2MB
        ]);

        $user = $request->user();
        $user->name = $request->name;

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $user->only('id', 'name', 'phone', 'role', 'avatar'),
        ]);
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function normalizePhone(string $phone): string
    {
        // Strip non-numeric
        $phone = preg_replace('/\D/', '', $phone);

        // Ivory Coast: 10 digits starting with 0. 
        // Based on the simulator screenshot (+225 07...), we should KEEP the 0.
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '+225' . $phone;
        }

        // Handle cases where 225 is already there
        if (str_starts_with($phone, '225')) {
            if (strlen($phone) === 13) return '+' . $phone; // +225 07...
            if (strlen($phone) === 12) return '+' . $phone; // +225 7...
        }

        if (strlen($phone) === 10) {
            return '+225' . $phone;
        }

        if (!str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }
}
