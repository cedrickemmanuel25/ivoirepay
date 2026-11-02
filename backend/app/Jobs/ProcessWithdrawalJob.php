<?php

namespace App\Jobs;

use App\Models\WithdrawalRequest;
use App\Services\AfricasTalkingService;
use App\Services\YengapayService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWithdrawalJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /** @var int Maximum queue attempts before failing */
    public int $tries = 3;

    /** @var int Delay in seconds between retries */
    public int $backoff = 30;

    public function __construct(
        private readonly WithdrawalRequest $withdrawal
    ) {}

    public function handle(YengapayService $yengapay, AfricasTalkingService $sms, \App\Services\NotificationService $notifications): void
    {
        $withdrawal = $this->withdrawal->fresh();

        // Guard: skip if already resolved (idempotency)
        if (! in_array($withdrawal->status, ['pending', 'processing'])) {
            return;
        }

        $withdrawal->update(['status' => 'processing']);

        try {
            $result = $yengapay->initiateWithdrawal(
                merchantId:   $withdrawal->merchant_id,
                amount:       (float) $withdrawal->amount,
                walletNumber: $withdrawal->wallet_number
            );

            if (($result['success'] ?? false) || ($result['status'] ?? '') === 'success') {
                // Deduct the balance from merchant (was reserved at request time — refund guard)
                // If balance was already debited at request, skip decrement. If not, decrement here.
                $withdrawal->update([
                    'status'       => 'completed',
                    'provider_ref' => $result['ref'] ?? $result['provider_ref'] ?? null,
                    'processed_at' => now(),
                ]);

                // Notify via NotificationService (in-app + SMS)
                $notifications->notifyWithdrawalCompleted($withdrawal->fresh());

                Log::info("Withdrawal {$withdrawal->id} completed.");

            } else {
                $this->markFailed($withdrawal, $result['message'] ?? 'Provider error.');
            }

        } catch (Exception $e) {
            Log::error("ProcessWithdrawalJob failed for withdrawal #{$withdrawal->id}: " . $e->getMessage());

            if ($this->attempts() >= $this->tries) {
                $this->markFailed($withdrawal, $e->getMessage());
            } else {
                // Will be retried by queue worker
                throw $e;
            }
        }
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function markFailed(WithdrawalRequest $withdrawal, string $reason): void
    {
        $withdrawal->update(['status' => 'failed']);

        // Refund the merchant's balance since the withdrawal did not go through
        $withdrawal->merchant()->increment('balance', $withdrawal->amount);

        Log::warning("Withdrawal #{$withdrawal->id} failed. Balance refunded. Reason: {$reason}");
    }
}
