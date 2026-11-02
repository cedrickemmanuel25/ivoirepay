<?php

namespace Tests\Feature;

use App\Models\KycDocument;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsIvoirePayData;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase, BuildsIvoirePayData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSms();
    }

    public function test_landing_page_is_public(): void
    {
        $this->get('/')->assertOk()->assertSee('IvoirePay');
    }

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_can_log_in_with_email_and_password(): void
    {
        User::factory()->admin()->create(['email' => 'admin@demo.test', 'pin_hash' => Hash::make('secret-demo')]);

        $this->post('/admin/login', ['email' => 'admin@demo.test', 'password' => 'secret-demo'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    public function test_non_admin_account_cannot_open_the_back_office(): void
    {
        // A merchant that somehow has an email must never get an admin session
        User::factory()->merchant()->create(['email' => 'merchant@demo.test', 'pin_hash' => Hash::make('1234')]);

        $this->post('/admin/login', ['email' => 'merchant@demo.test', 'password' => '1234'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_every_admin_screen_renders(): void
    {
        $merchant = $this->makeMerchant('pending', 5000);
        WithdrawalRequest::create([
            'merchant_id' => $merchant->id, 'amount' => 1000, 'wallet_number' => '0000000203',
            'wallet_type' => 'wave', 'status' => 'pending',
        ]);
        $admin = User::factory()->admin()->create();

        foreach ([
            '/admin/dashboard', '/admin/kyc', "/admin/kyc/{$merchant->id}", '/admin/transactions',
            '/admin/merchants', "/admin/merchants/{$merchant->id}", '/admin/withdrawals',
            '/admin/users', "/admin/users/{$merchant->user_id}", '/admin/notifications',
            '/admin/profile', '/admin/settings',
        ] as $url) {
            $this->actingAs($admin, 'admin')->get($url)->assertOk();
        }
    }

    public function test_admin_search_filters_work_on_mysql_compatible_sql(): void
    {
        $merchant = $this->makeMerchant('approved', 5000);
        WithdrawalRequest::create([
            'merchant_id' => $merchant->id, 'amount' => 1000, 'wallet_number' => '0000000203',
            'wallet_type' => 'wave', 'status' => 'pending',
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'admin')->get('/admin/users?search=' . urlencode($merchant->user->name))
            ->assertOk()->assertSee($merchant->user->name);
        $this->actingAs($admin, 'admin')->get('/admin/withdrawals?merchant=Boutique')
            ->assertOk()->assertSee($merchant->business_name);
    }

    public function test_kyc_documents_are_private_and_shown_to_admin_through_signed_urls(): void
    {
        // Real private disk (Storage::fake does not sign URLs); cleaned up below
        $merchant = $this->makeMerchant('pending');
        $path = 'public/kyc/phpunit-' . uniqid() . '/cni.png';
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $disk->put($path, 'fake-image');
        KycDocument::create([
            'merchant_id' => $merchant->id, 'document_type' => 'cni',
            'file_path' => $path, 'original_name' => 'cni.png',
        ]);

        try {
            $html = $this->actingAs(User::factory()->admin()->create(), 'admin')
                ->get("/admin/kyc/{$merchant->id}")->assertOk()->getContent();

            preg_match('#src="([^"]+cni\.png[^"]*)"#', $html, $m);
            $signedUrl = html_entity_decode($m[1] ?? '');
            $this->assertStringContainsString('signature=', $signedUrl);

            // The signed link works, the bare one is refused
            $this->get($signedUrl)->assertOk();
            $this->get(strtok($signedUrl, '?'))->assertForbidden();
        } finally {
            $disk->deleteDirectory(dirname($path));
        }
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'admin')->postJson("/admin/users/{$admin->id}/toggle")->assertForbidden();
        $this->assertTrue((bool) $admin->fresh()->is_active);
    }
}
