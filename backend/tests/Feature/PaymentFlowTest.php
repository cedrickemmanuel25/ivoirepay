<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsIvoirePayData;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase, BuildsIvoirePayData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSms();
        config([
            'services.yengapay.base_url'       => 'https://yengapay.test/api',
            'services.yengapay.api_key'        => 'pk_test_fake',
            'services.yengapay.organization_id' => 'org',
            'services.yengapay.project_id'     => 'proj',
            'services.yengapay.webhook_secret' => 'test-webhook-secret',
        ]);
        Http::fake([
            'yengapay.test/*' => Http::response(['id' => 'YP-REF-1', 'provider_ref' => 'YP-REF-1'], 200),
        ]);
    }

    private function signedWebhook(array $payload)
    {
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'test-webhook-secret');

        return $this->call('POST', '/api/webhooks/yengapay', [], [], [], [
            'CONTENT_TYPE'              => 'application/json',
            'HTTP_ACCEPT'               => 'application/json',
            'HTTP_X_YENGAPAY_SIGNATURE' => $signature,
        ], $body);
    }

    private function pendingTransaction(array $attributes = []): Transaction
    {
        $merchant = $attributes['merchant'] ?? $this->makeMerchant();
        unset($attributes['merchant']);

        return Transaction::create(array_merge([
            'reference'            => (string) \Illuminate\Support\Str::uuid(),
            'merchant_id'          => $merchant->id,
            'client_id'            => $this->makeClient()->id,
            'amount'               => 5000,
            'wallet_type'          => 'wave',
            'wallet_number'        => '0000000201',
            'status'               => 'pending',
            'payment_provider_ref' => 'YP-REF-1',
        ], $attributes));
    }

    public function test_client_can_initiate_payment_with_every_supported_wallet(): void
    {
        $merchant = $this->makeMerchant();
        Sanctum::actingAs($this->makeClient());

        foreach (['wave', 'djamo', 'moov', 'orange', 'mtn'] as $wallet) {
            $this->postJson('/api/transactions/initiate', [
                'merchant_id'   => $merchant->id,
                'amount'        => 2500,
                'wallet_type'   => $wallet,
                'wallet_number' => '0000000201',
            ])->assertCreated()->assertJsonPath('merchant_name', $merchant->business_name);
        }

        $this->assertSame(5, Transaction::count());
    }

    public function test_initiate_rejects_invalid_amount_and_unapproved_merchant(): void
    {
        Sanctum::actingAs($this->makeClient());

        $this->postJson('/api/transactions/initiate', [
            'merchant_id' => $this->makeMerchant()->id, 'amount' => 50,
            'wallet_type' => 'wave', 'wallet_number' => '0000000201',
        ])->assertStatus(422)->assertJsonValidationErrors('amount');

        $this->postJson('/api/transactions/initiate', [
            'merchant_id' => $this->makeMerchant('pending')->id, 'amount' => 1000,
            'wallet_type' => 'wave', 'wallet_number' => '0000000201',
        ])->assertForbidden();
    }

    public function test_simulated_confirmation_credits_merchant_exactly_once(): void
    {
        config(['services.yengapay.simulate' => true]);
        $client = $this->makeClient();
        $tx = $this->pendingTransaction(['client_id' => $client->id, 'payment_provider_ref' => null]);
        Sanctum::actingAs($client);

        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '1234', 'wallet_number' => '0000000201'])
            ->assertOk()->assertJsonPath('status', 'success');

        // Second confirmation of the same transaction must not pay twice
        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '1234', 'wallet_number' => '0000000201'])
            ->assertNotFound();

        $this->assertSame('success', $tx->fresh()->status);
        $this->assertEquals(5000, $tx->merchant->fresh()->balance);
    }

    public function test_confirmation_requires_correct_pin_and_ownership(): void
    {
        config(['services.yengapay.simulate' => true]);
        $tx = $this->pendingTransaction();

        Sanctum::actingAs($this->makeClient());
        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '1234', 'wallet_number' => '0000000201'])
            ->assertNotFound();

        Sanctum::actingAs($tx->client);
        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '9999', 'wallet_number' => '0000000201'])
            ->assertForbidden();

        $this->assertSame('pending', $tx->fresh()->status);
        $this->assertEquals(0, $tx->merchant->fresh()->balance);
    }

    public function test_debug_mode_alone_does_not_simulate_payments(): void
    {
        // A misconfigured production server (APP_DEBUG=true) must still wait for the provider webhook
        config(['app.env' => 'production', 'app.debug' => true, 'services.yengapay.simulate' => false]);
        $client = $this->makeClient();
        $tx = $this->pendingTransaction(['client_id' => $client->id, 'payment_provider_ref' => null]);
        Sanctum::actingAs($client);

        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '1234', 'wallet_number' => '0000000201'])
            ->assertOk()->assertJsonMissing(['status' => 'success']);

        // A retry while waiting for the webhook must not push a second payment request to the wallet
        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '1234', 'wallet_number' => '0000000201'])
            ->assertStatus(409);
        Http::assertSentCount(1);

        $this->assertSame('pending', $tx->fresh()->status);
        $this->assertEquals(0, $tx->merchant->fresh()->balance);
    }

    public function test_simulation_mode_never_calls_the_payment_provider(): void
    {
        config(['services.yengapay.simulate' => true]);
        $client = $this->makeClient();
        $tx = $this->pendingTransaction(['client_id' => $client->id, 'payment_provider_ref' => null]);
        Sanctum::actingAs($client);

        $this->postJson("/api/transactions/{$tx->id}/confirm", ['pin' => '1234', 'wallet_number' => '0000000201'])
            ->assertOk();

        Http::assertNothingSent();
        $this->assertStringStartsWith('SIMULATED-', $tx->fresh()->payment_provider_ref);
    }

    public function test_webhook_rejects_bad_signature(): void
    {
        $tx = $this->pendingTransaction();

        $this->call('POST', '/api/webhooks/yengapay', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_YENGAPAY_SIGNATURE' => 'forged',
        ], json_encode(['provider_ref' => 'YP-REF-1', 'status' => 'success']))->assertUnauthorized();

        $this->assertSame('pending', $tx->fresh()->status);
    }

    public function test_webhook_is_refused_when_no_secret_is_configured(): void
    {
        // Without a secret, an attacker could sign payloads with an empty HMAC key
        config(['services.yengapay.webhook_secret' => null]);
        $tx = $this->pendingTransaction();
        $body = json_encode(['provider_ref' => 'YP-REF-1', 'status' => 'success']);

        $this->call('POST', '/api/webhooks/yengapay', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_YENGAPAY_SIGNATURE' => hash_hmac('sha256', $body, ''),
        ], $body)->assertUnauthorized();

        $this->assertEquals(0, $tx->merchant->fresh()->balance);
    }

    public function test_replayed_success_webhook_credits_merchant_once(): void
    {
        $tx = $this->pendingTransaction();

        $this->signedWebhook(['provider_ref' => 'YP-REF-1', 'status' => 'success'])->assertOk();
        $this->signedWebhook(['provider_ref' => 'YP-REF-1', 'status' => 'success'])->assertOk();

        $this->assertSame('success', $tx->fresh()->status);
        $this->assertEquals(5000, $tx->merchant->fresh()->balance);
    }

    public function test_late_failure_webhook_cannot_overwrite_a_settled_payment(): void
    {
        $tx = $this->pendingTransaction();

        $this->signedWebhook(['provider_ref' => 'YP-REF-1', 'status' => 'success'])->assertOk();
        $this->signedWebhook(['provider_ref' => 'YP-REF-1', 'status' => 'failed'])->assertOk();

        $this->assertSame('success', $tx->fresh()->status);
        $this->assertEquals(5000, $tx->merchant->fresh()->balance);
    }

    public function test_webhook_rejects_unknown_status(): void
    {
        $tx = $this->pendingTransaction();

        $this->signedWebhook(['provider_ref' => 'YP-REF-1', 'status' => 'hacked'])->assertStatus(422);

        $this->assertSame('pending', $tx->fresh()->status);
    }

    public function test_client_only_sees_own_transactions(): void
    {
        $mine = $this->pendingTransaction();
        $other = $this->pendingTransaction();
        Sanctum::actingAs($mine->client);

        $this->getJson('/api/transactions')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.merchant_name', $mine->merchant->business_name);
        $this->getJson("/api/transactions/{$other->id}")->assertNotFound();
    }

    public function test_public_merchant_lookup_only_exposes_approved_merchants(): void
    {
        $approved = $this->makeMerchant();
        $pending = $this->makeMerchant('pending');

        $this->getJson("/api/merchants/{$approved->id}")->assertOk()
            ->assertJsonPath('business_name', $approved->business_name)
            ->assertJsonMissingPath('balance');
        $this->getJson("/api/merchants/{$pending->id}")->assertNotFound();
    }
}
