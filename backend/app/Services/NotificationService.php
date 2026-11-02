<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function __construct(
        private readonly AfricasTalkingService $sms
    ) {}

    // ─── Send an in-app notification to a single user ─────────────────────────

    public function sendInApp(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    // ─── Send an SMS to a phone number ────────────────────────────────────────

    public function sendSms(string $phone, string $message): bool
    {
        return $this->sms->sendMessage($phone, $message);
    }

    // ─── Broadcast in-app notification to a role group ────────────────────────

    public function sendToGroup(string $role, string $title, string $message, string $type = 'broadcast', array $data = []): void
    {
        $query = $role === 'all'
            ? User::query()
            : User::where('role', $role);

        $query->where('is_active', true)->each(function (User $user) use ($type, $title, $message, $data) {
            $this->sendInApp($user->id, $type, $title, $message, $data);
        });
    }

    // ─── Domain-specific convenience methods ─────────────────────────────────

    public function notifyTransactionSuccess(\App\Models\Transaction $transaction): void
    {
        $amount = number_format($transaction->amount, 0, ',', ' ');

        // In-app to merchant
        $this->sendInApp(
            userId:  $transaction->merchant->user_id,
            type:    'payment_received',
            title:   'Paiement reçu',
            message: "Vous avez reçu {$amount} FCFA.",
            data:    ['transaction_id' => $transaction->id]
        );

        // SMS to client
        if ($transaction->client?->phone) {
            $this->sendSms(
                phone:   $transaction->client->phone,
                message: "IvoirePay : Paiement de {$amount} FCFA effectué avec succès chez {$transaction->merchant->business_name}. Ref: {$transaction->reference}"
            );
        }
    }

    public function notifyKycResult(\App\Models\Merchant $merchant, string $status, ?string $reason = null): void
    {
        $phone = $merchant->user->phone;

        if ($status === 'approved') {
            $this->sendInApp(
                userId:  $merchant->user_id,
                type:    'kyc_approved',
                title:   'KYC approuvé',
                message: 'Félicitations ! Votre compte commerçant est maintenant actif.',
                data:    ['merchant_id' => $merchant->id]
            );
            $this->sendSms($phone, 'IvoirePay : Votre dossier KYC a été approuvé. Votre compte commerçant est actif !');
        } else {
            $this->sendInApp(
                userId:  $merchant->user_id,
                type:    'kyc_rejected',
                title:   'KYC rejeté',
                message: "Votre dossier KYC a été rejeté. Motif : {$reason}",
                data:    ['merchant_id' => $merchant->id, 'reason' => $reason]
            );
            $this->sendSms($phone, "IvoirePay : Votre dossier KYC a été rejeté. Motif : {$reason}. Contactez le support pour plus d'informations.");
        }
    }

    public function notifyWithdrawalCompleted(\App\Models\WithdrawalRequest $withdrawal): void
    {
        $amount = number_format($withdrawal->amount, 0, ',', ' ');
        $phone  = $withdrawal->merchant->user->phone;

        $this->sendInApp(
            userId:  $withdrawal->merchant->user_id,
            type:    'withdrawal_completed',
            title:   'Retrait effectué',
            message: "Votre retrait de {$amount} FCFA a été effectué avec succès.",
            data:    ['withdrawal_id' => $withdrawal->id]
        );

        $this->sendSms($phone, "IvoirePay : Retrait de {$amount} FCFA effectué avec succès. Ref: {$withdrawal->provider_ref}");
    }

    public function notifyAdminsNewKyc(\App\Models\Merchant $merchant): void
    {
        $this->sendToGroup(
            role:    'admin',
            type:    'kyc_submitted',
            title:   'Nouveau dossier KYC',
            message: "Le commerçant \"{$merchant->business_name}\" a soumis son dossier KYC.",
            data:    ['merchant_id' => $merchant->id]
        );
    }

    public function notifyAdminsWithdrawalRequest(\App\Models\WithdrawalRequest $withdrawal): void
    {
        $amount = number_format($withdrawal->amount, 0, ',', ' ');
        $this->sendToGroup(
            role:    'admin',
            type:    'withdrawal_requested',
            title:   'Demande de retrait',
            message: "Un commerçant demande un retrait de {$amount} FCFA.",
            data:    ['withdrawal_id' => $withdrawal->id]
        );
    }
}
