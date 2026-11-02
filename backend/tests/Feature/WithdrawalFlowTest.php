<?php

namespace Tests\Feature;

use App\Jobs\ProcessWithdrawalJob;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsIvoirePayData;
use Tests\TestCase;

class WithdrawalFlowTest extends TestCase
{
    use RefreshDatabase, BuildsIvoirePayData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSms();
        config(['services.yengapay.base_url' => 'https://yengapay.test/api', 'services.yengapay.api_key' => 'pk_test_fake']);
    }

    private function request(array $overrides = [])
    {
        return $this->postJson('/api/merchant/withdrawal', array_merge([
            'amount'        => 3000,
            'wallet_type'   => 'wave',
            'wallet_number' => '0000000202',
            'pin'           => '1234',
        ], $overrides));
    }

    public function test_simulated_withdrawal_reserves_balance_and_completes(): void
    {
        config(['services.yengapay.simulate' => true]);
        $merchant = $this->makeMerchant('approved', 10000);
        Sanctum::actingAs($merchant->user);

        $this->request()->assertCreated();

        $this->assertEquals(7000, $merchant->fresh()->balance);
        $this->assertSame('completed', WithdrawalRequest::first()->status);
    }

    public function test_withdrawal_requires_the_merchant_pin(): void
    {
        config(['services.yengapay.simulate' => true]);
        $merchant = $this->makeMerchant('approved', 10000);
        Sanctum::actingAs($merchant->user);

        $this->request(['pin' => null])->assertStatus(422)->assertJsonValidationErrors('pin');
        $this->request(['pin' => '0000'])->assertForbidden();

        $this->assertEquals(10000, $merchant->fresh()->balance);
        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_withdrawal_cannot_exceed_balance_or_bypass_kyc(): void
    {
        $merchant = $this->makeMerchant('approved', 2000);
        Sanctum::actingAs($merchant->user);
        $this->request(['amount' => 5000])->assertStatus(422);

        $pending = $this->makeMerchant('pending', 50000);
        Sanctum::actingAs($pending->user);
        $this->request()->assertForbidden();

        $client = $this->makeClient();
        Sanctum::actingAs($client);
        $this->request()->assertForbidden();

        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_production_withdrawal_is_queued_not_auto_completed(): void
    {
        Queue::fake();
        config(['app.env' => 'production', 'app.debug' => true, 'services.yengapay.simulate' => false]);
        $merchant = $this->makeMerchant('approved', 10000);
        Sanctum::actingAs($merchant->user);

        $this->request()->assertCreated()->assertJsonPath('status', 'pending');

        Queue::assertPushed(ProcessWithdrawalJob::class);
        $this->assertEquals(7000, $merchant->fresh()->balance);
    }

    public function test_failed_payout_refunds_once_and_admin_cannot_refund_again(): void
    {
        Http::fake(['yengapay.test/*' => Http::response(['message' => 'refused'], 400)]);
        $merchant = $this->makeMerchant('approved', 7000);
        $withdrawal = WithdrawalRequest::create([
            'merchant_id' => $merchant->id, 'amount' => 3000, 'wallet_number' => '0000000202',
            'wallet_type' => 'wave', 'status' => 'pending',
        ]);

        dispatch_sync(new ProcessWithdrawalJob($withdrawal));
        $this->assertSame('failed', $withdrawal->fresh()->status);
        $this->assertEquals(10000, $merchant->fresh()->balance);

        // Running the job again (duplicate dispatch) must not refund a second time
        dispatch_sync(new ProcessWithdrawalJob($withdrawal));
        $this->actingAs(\App\Models\User::factory()->admin()->create(), 'admin')
            ->postJson(route('admin.withdrawals.cancel', $withdrawal))->assertStatus(400);

        $this->assertEquals(10000, $merchant->fresh()->balance);
    }

    public function test_admin_cancel_refunds_pending_withdrawal_exactly_once(): void
    {
        $merchant = $this->makeMerchant('approved', 7000);
        $withdrawal = WithdrawalRequest::create([
            'merchant_id' => $merchant->id, 'amount' => 3000, 'wallet_number' => '0000000202',
            'wallet_type' => 'wave', 'status' => 'pending',
        ]);
        $admin = \App\Models\User::factory()->admin()->create();

        $this->actingAs($admin, 'admin')->postJson(route('admin.withdrawals.cancel', $withdrawal))->assertOk();
        $this->actingAs($admin, 'admin')->postJson(route('admin.withdrawals.cancel', $withdrawal))->assertStatus(400);

        $this->assertEquals(10000, $merchant->fresh()->balance);
    }

    public function test_concurrent_job_does_not_send_a_second_payout(): void
    {
        Http::fake(['yengapay.test/*' => Http::response(['status' => 'success', 'ref' => 'PAYOUT-1'], 200)]);
        $merchant = $this->makeMerchant('approved', 7000);
        // Another worker already claimed this withdrawal and is talking to the provider
        $withdrawal = WithdrawalRequest::create([
            'merchant_id' => $merchant->id, 'amount' => 3000, 'wallet_number' => '0000000202',
            'wallet_type' => 'wave', 'status' => 'processing',
        ]);

        dispatch_sync(new ProcessWithdrawalJob($withdrawal));

        Http::assertNothingSent();
        $this->assertSame('processing', $withdrawal->fresh()->status);
    }
}
