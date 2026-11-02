<?php

namespace Tests\Feature;

use App\Models\KycDocument;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsIvoirePayData;
use Tests\TestCase;

class AuthAndKycFlowTest extends TestCase
{
    use RefreshDatabase, BuildsIvoirePayData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSms();
    }

    public function test_phone_registration_with_otp_then_pin_login(): void
    {
        $this->postJson('/api/auth/send-otp', ['phone' => '0000000099', 'type' => 'registration'])
            ->assertOk()->assertJsonPath('phone', '+2250000000099');

        $code = OtpCode::where('phone', '+2250000000099')->value('code');

        $this->postJson('/api/auth/verify-otp', ['phone' => '0000000099', 'code' => $code, 'type' => 'registration'])
            ->assertOk()->assertJsonPath('is_new_user', true);

        $token = $this->postJson('/api/auth/register', ['phone' => '0000000099', 'name' => 'Awa Démo', 'role' => 'client'])
            ->assertCreated()->assertJsonPath('user.has_pin', false)->json('token');

        $this->withToken($token)->postJson('/api/auth/setup-pin', ['pin' => '2580', 'pin_confirmation' => '2580'])->assertOk();

        $this->postJson('/api/auth/login-pin', ['phone' => '0000000099', 'pin' => '2580'])
            ->assertOk()->assertJsonPath('user.role', 'client')->assertJsonPath('user.has_pin', true);
    }

    public function test_registration_is_refused_without_verified_otp(): void
    {
        $this->postJson('/api/auth/register', ['phone' => '0000000098', 'name' => 'Sans OTP', 'role' => 'client'])
            ->assertStatus(422);
        $this->assertSame(0, User::count());
    }

    public function test_wrong_otp_is_counted_and_blocked_after_max_attempts(): void
    {
        $this->postJson('/api/auth/send-otp', ['phone' => '0000000097', 'type' => 'login'])->assertOk();
        $real = OtpCode::first()->code;
        $wrong = $real === '000000' ? '111111' : '000000';

        foreach (range(1, 3) as $i) {
            $this->postJson('/api/auth/verify-otp', ['phone' => '0000000097', 'code' => $wrong, 'type' => 'login'])->assertStatus(422);
        }
        $this->postJson('/api/auth/verify-otp', ['phone' => '0000000097', 'code' => $real, 'type' => 'login'])->assertStatus(429);
    }

    public function test_otp_codes_are_not_written_to_application_logs(): void
    {
        \Illuminate\Support\Facades\Log::spy();

        $this->postJson('/api/auth/send-otp', ['phone' => '0000000096', 'type' => 'login'])->assertOk();
        $code = OtpCode::first()->code;
        $this->postJson('/api/auth/verify-otp', ['phone' => '0000000096', 'code' => $code, 'type' => 'login']);

        \Illuminate\Support\Facades\Log::shouldNotHaveReceived('info', [\Mockery::on(fn ($msg) => str_contains((string) $msg, $code))]);
        $this->assertTrue(true);
    }

    public function test_pin_brute_force_suspends_the_account(): void
    {
        $user = $this->makeClient(['phone' => '+2250000000095']);

        foreach (range(1, 4) as $i) {
            $this->postJson('/api/auth/login-pin', ['phone' => '0000000095', 'pin' => '0000'])->assertUnauthorized();
        }
        $this->postJson('/api/auth/login-pin', ['phone' => '0000000095', 'pin' => '0000'])->assertForbidden();
        $this->postJson('/api/auth/login-pin', ['phone' => '0000000095', 'pin' => '1234'])->assertForbidden();
        $this->assertFalse((bool) $user->fresh()->is_active);
    }

    public function test_protected_api_requires_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->getJson('/api/transactions')->assertUnauthorized();
        $this->postJson('/api/merchant/withdrawal')->assertUnauthorized();
    }

    public function test_user_endpoint_never_leaks_pin_hash(): void
    {
        Sanctum::actingAs($this->makeClient());

        $this->getJson('/api/user')->assertOk()->assertJsonMissingPath('pin_hash')->assertJsonPath('has_pin', true);
    }

    public function test_merchant_kyc_submission_stores_documents_privately(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->merchant()->create(['pin_hash' => null]);
        Sanctum::actingAs($user);

        $this->post('/api/merchant/kyc/submit', [
            'business_name'    => 'Maquis Démo',
            'business_address' => 'Plateau, Abidjan',
            'cni_number'       => 'CI-000-DEMO',
            'documents'        => [UploadedFile::fake()->image('cni.jpg')],
            'document_types'   => ['cni'],
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('status', 'pending');

        $doc = KycDocument::first();
        Storage::disk('local')->assertExists($doc->file_path);
        Storage::disk('public')->assertMissing($doc->file_path);

        $this->getJson('/api/merchant/kyc/status')->assertOk()->assertJsonPath('kyc_status', 'pending');
        // QR code is only delivered after approval
        $this->getJson('/api/merchant/qr-code')->assertForbidden();
    }

    public function test_merchant_dashboard_and_notifications(): void
    {
        $merchant = $this->makeMerchant('approved', 4200);
        $merchant->user->notifications()->create(['type' => 'demo', 'title' => 'Bienvenue', 'message' => 'Compte actif']);
        Sanctum::actingAs($merchant->user);

        $this->getJson('/api/merchant/dashboard')->assertOk()
            ->assertJsonPath('balance', 4200)->assertJsonCount(7, 'chart_data');
        $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('unread_count', 1);
        $this->postJson('/api/notifications/read-all')->assertOk();
        $this->getJson('/api/notifications/unread-count')->assertJsonPath('unread_count', 0);
    }
}
