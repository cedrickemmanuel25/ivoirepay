<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AfricasTalkingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public string $target,
        public array $channels,
        public string $message,
        public ?int $specificUserId = null
    ) {}

    public function handle(AfricasTalkingService $sms): void
    {
        $users = $this->getTargetUsers();
        
        foreach ($users as $user) {
            try {
                // SMS Channel
                if (!empty($this->channels['sms'])) {
                    if ($user->phone) {
                        $sms->sendMessage($user->phone, $this->message);
                    }
                }

                // In-App Channel
                if (!empty($this->channels['inapp'])) {
                    $user->notifications()->create([
                        'type' => 'admin_broadcast',
                        'title' => 'Message de l\'administrateur',
                        'message' => $this->message,
                        'data' => [],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send bulk notification to user {$user->id}: " . $e->getMessage());
                // Continue with next user
            }
        }
    }

    private function getTargetUsers()
    {
        if ($this->target === 'specific' && $this->specificUserId) {
            $user = User::find($this->specificUserId);
            return $user ? [$user] : [];
        }

        $query = User::where('is_active', true);

        if ($this->target === 'clients') {
            $query->where('role', 'client');
        } elseif ($this->target === 'merchants') {
            $query->where('role', 'merchant');
        } elseif ($this->target === 'all') {
            // Already handled, maybe exclude internal admins?
            $query->whereIn('role', ['client', 'merchant']);
        }

        return $query->cursor(); // Use cursor for memory efficiency with many users
    }
}
