<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        
        // Dummy activity log for now
        $activities = collect([
            (object)[
                'action' => 'Connexion réussie',
                'target' => 'Système',
                'ip' => request()->ip(),
                'browser' => request()->userAgent(),
                'date' => now()->subMinutes(12)
            ],
            (object)[
                'action' => 'Mise à jour statut utilisateur',
                'target' => 'User ID: 42',
                'ip' => '192.168.1.1',
                'browser' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'date' => now()->subHours(2)
            ],
            (object)[
                'action' => 'Envoi notification massive',
                'target' => 'Tous les clients',
                'ip' => request()->ip(),
                'browser' => request()->userAgent(),
                'date' => now()->subDays(1)
            ]
        ]);

        return view('admin.profile.index', compact('user', 'activities'));
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Vos informations ont été mises à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'password.regex' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.'
        ]);

        $user = Auth::guard('admin')->user() ?? Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Votre mot de passe a été modifié avec succès.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048' // max 2MB
        ]);

        $user = Auth::guard('admin')->user() ?? Auth::user();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // Delete old avatar if necessary (assuming it was stored locally)
            if ($user->avatar && str_starts_with($user->avatar, '/storage/avatars/')) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $user->update([
                'avatar' => '/storage/' . $path
            ]);

            return response()->json([
                'success' => true,
                'avatar_url' => $user->avatar,
                'message' => 'Avatar mis à jour'
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Aucun fichier reçu'], 400);
    }
}
