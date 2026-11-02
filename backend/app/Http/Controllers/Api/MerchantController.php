<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWithdrawalJob;
use App\Models\Setting;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use App\Services\QrCodeService;
use App\Services\YengapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    public function __construct(
        private readonly QrCodeService   $qrService,
        private readonly YengapayService $yengapay,
        private readonly NotificationService $notifications
    ) {}

    // ─── Merchant Profile ────────────────────────────────────────────────────

    public function profile(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['message' => 'Profil commerçant introuvable.'], 404);
        }

        return response()->json([
            'id'               => $merchant->id,
            'business_name'    => $merchant->business_name,
            'business_address' => $merchant->business_address,
            'kyc_status'       => $merchant->kyc_status,
            'balance'          => (float) $merchant->balance,
            'qr_code_url'      => $merchant->qr_code_url,
            'rccm_number'      => $merchant->rccm_number,
            'cni_number'       => $merchant->cni_number,
        ]);
    }

    // ─── QR Code ─────────────────────────────────────────────────────────────

    public function qrCode(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['message' => 'Profil commerçant introuvable.'], 404);
        }

        if ($merchant->kyc_status !== 'approved') {
            return response()->json(['message' => 'QR Code disponible uniquement après validation KYC.'], 403);
        }

        if (! $merchant->qr_code_path || ! \Illuminate\Support\Facades\Storage::disk('public')->exists($merchant->qr_code_path)) {
            $this->qrService->generateForMerchant($merchant);
            $merchant->refresh();
        }

        return response()->json(['qr_code_url' => $merchant->qr_code_url]);
    }

    // ─── Transactions History ─────────────────────────────────────────────────

    public function transactions(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['message' => 'Profil commerçant introuvable.'], 404);
        }

        $query = $merchant->transactions()->with('client:id,name,phone');

        if ($request->filter === 'today') {
            $query->whereDate('created_at', now()->today());
        } elseif ($request->filter === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($request->filter === 'month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function withdrawals(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['message' => 'Profil commerçant introuvable.'], 404);
        }

        return response()->json(
            $merchant->withdrawalRequests()->latest()->paginate(20)
        );
    }

    // ─── Dashboard Stats ─────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (! $merchant) {
            return response()->json(['message' => 'Profil commerçant introuvable.'], 404);
        }

        $base = $merchant->transactions()->successful();

        // 7-day chart data
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date'    => $date,
                'revenue' => (float) (clone $base)->whereDate('created_at', $date)->sum('amount'),
            ];
        }

        return response()->json([
            'balance'    => (float) $merchant->balance,
            'today'      => [
                'revenue' => (float) (clone $base)->today()->sum('amount'),
                'count'   => (clone $base)->today()->count(),
            ],
            'this_month' => [
                'revenue' => (float) (clone $base)->thisMonth()->sum('amount'),
                'count'   => (clone $base)->thisMonth()->count(),
            ],
            'chart_data' => $chartData,
        ]);
    }

    // ─── Withdrawal Request ───────────────────────────────────────────────────

    public function withdrawal(Request $request)
    {
        $minWithdrawal = (float) (Setting::where('key', 'min_withdrawal')->value('value') ?? 1000);

        $request->validate([
            'amount'        => "required|numeric|min:{$minWithdrawal}",
            'wallet_number' => 'required|string',
            'wallet_type'   => 'required|in:wave,djamo,moov,orange,mtn',
        ]);

        $merchant = $request->user()->merchant;

        if (! $merchant || $merchant->kyc_status !== 'approved') {
            return response()->json(['message' => 'KYC non validé.'], 403);
        }

        if ($merchant->balance < $request->amount) {
            return response()->json(['message' => 'Solde insuffisant.'], 422);
        }

        // Reserve the funds immediately — ProcessWithdrawalJob will refund on failure
        $merchant->decrement('balance', $request->amount);

        $withdrawal = WithdrawalRequest::create([
            'merchant_id'   => $merchant->id,
            'amount'        => $request->amount,
            'wallet_number' => $request->wallet_number,
            'wallet_type'   => $request->wallet_type,
            'status'        => 'pending',
        ]);

        // Dispatch handle — In local, process immediately. In prod, use Queue.
        if (config('app.env') === 'local' || config('app.debug')) {
            $this->completeWithdrawal($withdrawal);
        } else {
            ProcessWithdrawalJob::dispatch($withdrawal);
        }

        // Also notify admins of the new request (even if auto-completed)
        $this->notifications->notifyAdminsWithdrawalRequest($withdrawal);

        return response()->json([
            'message'       => 'Demande de retrait en cours de traitement.',
            'withdrawal_id' => $withdrawal->id,
            'amount'        => $withdrawal->amount,
            'status'        => $withdrawal->status,
        ], 201);
    }

    /**
     * Finalize a withdrawal immediately (Local/Dev use only)
     */
    private function completeWithdrawal(WithdrawalRequest $withdrawal)
    {
        $withdrawal->update([
            'status'       => 'completed',
            'provider_ref' => 'LOCAL_MOCK_' . strtoupper(bin2hex(random_bytes(4))),
            'processed_at' => now(),
        ]);

        $this->notifications->notifyWithdrawalCompleted($withdrawal->fresh());
    }
}
