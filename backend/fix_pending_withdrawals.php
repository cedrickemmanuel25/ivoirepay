<?php

use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pendingWithdrawals = WithdrawalRequest::where('status', 'pending')->get();
$notifications = app(NotificationService::class);

foreach ($pendingWithdrawals as $withdrawal) {
    echo "Processing withdrawal #{$withdrawal->id} for {$withdrawal->amount} FCFA...\n";
    
    $withdrawal->update([
        'status'       => 'completed',
        'provider_ref' => 'LOCAL_MOCK_CLEANUP_' . strtoupper(bin2hex(random_bytes(4))),
        'processed_at' => now(),
    ]);

    $notifications->notifyWithdrawalCompleted($withdrawal->fresh());
    echo "Completed!\n";
}

echo "Done. " . $pendingWithdrawals->count() . " withdrawal(s) finalized.\n";
