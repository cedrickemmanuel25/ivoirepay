<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Portfolio / local demo data — 100% fictitious.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Phone numbers use the +225 00 00 xx xx xx range (no Ivorian operator prefix),
 * no real identity document is stored and no provider is ever called.
 * Idempotent: re-running it does not duplicate data.
 *
 * Demo accounts (PIN 1234):
 *   Client   : 0000000001
 *   Merchant : 0000000002 (KYC approved)  /  0000000003 (KYC pending)
 */
class DemoSeeder extends Seeder
{
    public function run(QrCodeService $qrService): void
    {
        $this->call([AdminSeeder::class, SettingsSeeder::class]);

        $client = $this->user('+2250000000001', 'Client Démo', 'client');

        $merchants = [
            $this->merchant('+2250000000002', 'Commerçant Démo', 'Maquis Démo Cocody', 'Cocody, Abidjan', 'approved'),
            $this->merchant('+2250000000004', 'Commerçant Démo 2', 'Boutique Démo Plateau', 'Plateau, Abidjan', 'approved'),
            $this->merchant('+2250000000003', 'Commerçant en attente', 'Atelier Démo Yopougon', 'Yopougon, Abidjan', 'pending'),
        ];

        foreach ($merchants as $merchant) {
            if ($merchant->kyc_status === 'approved') {
                $qrService->generateForMerchant($merchant);
            }
        }

        if (Transaction::where('client_id', $client->id)->exists()) {
            return; // already seeded
        }

        // [merchant index, amount, wallet, status, days ago, hour]
        $payments = [
            [0, 3500,  'wave',   'success', 0, 9],
            [1, 12000, 'orange', 'success', 0, 12],
            [0, 1500,  'mtn',    'success', 1, 19],
            [1, 7500,  'moov',   'success', 2, 11],
            [0, 25000, 'djamo',  'success', 3, 15],
            [1, 4200,  'wave',   'failed',  4, 10],
            [0, 9000,  'wave',   'success', 5, 18],
            [1, 2000,  'orange', 'success', 6, 8],
        ];

        foreach ($payments as [$m, $amount, $wallet, $status, $daysAgo, $hour]) {
            $at = now()->subDays($daysAgo)->setTime($hour, rand(0, 59));
            $tx = Transaction::create([
                'reference'            => (string) Str::uuid(),
                'merchant_id'          => $merchants[$m]->id,
                'client_id'            => $client->id,
                'amount'               => $amount,
                'wallet_type'          => $wallet,
                'wallet_number'        => '0000000101',
                'status'               => $status,
                'commission_amount'    => 0,
                'payment_provider_ref' => 'SIMULATED-' . strtoupper(Str::random(10)),
                'metadata'             => ['simulated' => true, 'demo' => true],
            ]);
            $tx->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }

        WithdrawalRequest::create([
            'merchant_id' => $merchants[0]->id, 'amount' => 10000, 'wallet_number' => '0000000102',
            'wallet_type' => 'wave', 'status' => 'completed', 'provider_ref' => 'LOCAL_MOCK_DEMO', 'processed_at' => now()->subDay(),
        ]);
        WithdrawalRequest::create([
            'merchant_id' => $merchants[1]->id, 'amount' => 5000, 'wallet_number' => '0000000103',
            'wallet_type' => 'orange', 'status' => 'pending',
        ]);

        // Balances stay consistent with the ledger: successful payments minus reserved/paid withdrawals
        foreach ($merchants as $merchant) {
            $received = $merchant->transactions()->successful()->sum('amount');
            $withdrawn = $merchant->withdrawalRequests()->where('status', '!=', 'failed')->sum('amount');
            $merchant->update(['balance' => $received - $withdrawn]);
        }

        $client->notifications()->create([
            'type' => 'welcome', 'title' => 'Bienvenue sur IvoirePay',
            'message' => 'Scannez le QR code d\'un commerçant pour payer en toute sécurité.',
        ]);
        $merchants[0]->user->notifications()->create([
            'type' => 'payment_received', 'title' => 'Paiement reçu', 'message' => 'Vous avez reçu 3 500 FCFA.',
        ]);
    }

    private function user(string $phone, string $name, string $role): User
    {
        return User::updateOrCreate(['phone' => $phone], [
            'name'              => $name,
            'role'              => $role,
            'pin_hash'          => Hash::make('1234'),
            'is_active'         => true,
            'phone_verified_at' => now(),
        ]);
    }

    private function merchant(string $phone, string $owner, string $business, string $address, string $kyc): Merchant
    {
        $user = $this->user($phone, $owner, 'merchant');

        return Merchant::updateOrCreate(['user_id' => $user->id], [
            'business_name'    => $business,
            'business_address' => $address,
            'cni_number'       => 'DEMO-CNI-' . $user->id,
            'rccm_number'      => $kyc === 'approved' ? 'DEMO-RCCM-' . $user->id : null,
            'kyc_status'       => $kyc,
            'approved_at'      => $kyc === 'approved' ? now()->subDays(10) : null,
        ]);
    }
}
