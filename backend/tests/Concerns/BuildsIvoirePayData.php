<?php

namespace Tests\Concerns;

use App\Models\Merchant;
use App\Models\User;
use App\Services\AfricasTalkingService;

/**
 * Shared fixtures for IvoirePay feature tests.
 * Every phone number / identity here is fictitious.
 */
trait BuildsIvoirePayData
{
    protected function fakeSms(): void
    {
        // Never reach Africa's Talking from tests
        $this->mock(AfricasTalkingService::class, function ($mock) {
            $mock->shouldReceive('sendOtp')->andReturn(true);
            $mock->shouldReceive('sendMessage')->andReturn(true);
            $mock->shouldReceive('sendKycResult')->andReturn(true);
        });
    }

    protected function makeClient(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function makeMerchant(string $kycStatus = 'approved', float $balance = 0): Merchant
    {
        $user = User::factory()->merchant()->create();

        return Merchant::create([
            'user_id'          => $user->id,
            'business_name'    => 'Boutique Démo ' . $user->id,
            'business_address' => 'Cocody, Abidjan',
            'cni_number'       => 'CI-DEMO-' . $user->id,
            'balance'          => $balance,
            'kyc_status'       => $kycStatus,
            'approved_at'      => $kycStatus === 'approved' ? now() : null,
        ]);
    }
}
