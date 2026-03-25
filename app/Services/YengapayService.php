<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YengapayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $organizationId;
    private string $projectId;

    public function __construct()
    {
        $this->baseUrl        = rtrim(config('services.yengapay.base_url'), '/');
        $this->apiKey         = config('services.yengapay.api_key');
        $this->organizationId = config('services.yengapay.organization_id');
        $this->projectId      = config('services.yengapay.project_id');
    }

    // ─── Initiate a payment request sent to the customer wallet ──────────────

    public function initiatePayment(
        float  $amount,
        string $walletType,
        string $walletNumber,
        string $reference
    ): array {
        try {
            $url = "{$this->baseUrl}/groups/{$this->organizationId}/payment-intent/{$this->projectId}";

            Log::info('YengapayService::initiatePayment - Envoi de la requête', [
                'url' => $url,
                'amount' => $amount,
                'wallet_type' => $walletType,
                'wallet_number' => $walletNumber,
                'reference' => $reference,
            ]);

            $response = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                ])
                ->withoutVerifying()
                ->post($url, [
                    'paymentAmount' => (int) $amount,
                    'reference'     => $reference,
                    // Use 'test' if key starts with pk_test_, else 'production'
                    'apiEnv'        => (str_starts_with($this->apiKey, 'pk_test_') || strpos($this->apiKey, 'test') !== false) ? 'test' : 'production',
                    'articles'      => [
                        [
                            'title'       => 'Paiement IvoirePay',
                            'description' => 'Paiement de marchand via QR Code',
                            'price'       => (int) $amount,
                        ]
                    ],
                    'customerNumber' => $walletNumber,
                    'paymentSource'  => match(strtolower($walletType)) {
                        'wave'   => 'wave_ci',
                        'moov'   => 'moov_money_ci',
                        'djamo'  => 'djamo_ci',
                        'orange' => 'orange_money_ci',
                        'mtn'    => 'mtn_money_ci',
                        default  => strtolower($walletType),
                    },
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return array_merge(['status' => 'success'], $data);
            }

            Log::error('YengapayService::initiatePayment failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return ['status' => 'failed', 'message' => $response->json('message', 'Erreur de paiement.')];
        } catch (\Exception $e) {
            Log::error('YengapayService::initiatePayment exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    // ─── Check current status of a provider payment reference ────────────────

    public function checkStatus(string $providerRef): string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get("{$this->baseUrl}/payments/{$providerRef}/status");

            return $response->json('status', 'failed');
        } catch (\Exception $e) {
            Log::error('YengapayService::checkStatus exception', ['error' => $e->getMessage()]);
            return 'failed';
        }
    }

    // ─── Initiate a withdrawal to a merchant wallet ───────────────────────────

    public function initiateWithdrawal(int $merchantId, float $amount, string $walletNumber): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/withdrawals/initiate", [
                    'merchant_id'   => $merchantId,
                    'amount'        => $amount,
                    'currency'      => 'XOF',
                    'wallet_number' => $walletNumber,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('YengapayService::initiateWithdrawal failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return ['status' => 'failed', 'message' => $response->json('message', 'Erreur de retrait.')];
        } catch (\Exception $e) {
            Log::error('YengapayService::initiateWithdrawal exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    // ─── HMAC Signature Verification for webhooks ─────────────────────────────

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret   = config('services.yengapay.webhook_secret');
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}
