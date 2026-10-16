<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSettingsNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_views_render_unified_sidebar_navigation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Profile & Settings
        $profileRes = $this->get(route('profile.edit'));
        $profileRes->assertOk()
            ->assertSee('Account Settings')
            ->assertSee('Profile Information')
            ->assertSee('Security & Password')
            ->assertSee('Notification Preferences')
            ->assertSee(route('account.memberships.index'))
            ->assertSee(route('account.payment-history.index'))
            ->assertSee(route('account.referrals'));

        // 2. Memberships
        $membershipRes = $this->get(route('account.memberships.index'));
        $membershipRes->assertOk()
            ->assertSee('Account Settings')
            ->assertSee(route('profile.edit'));

        // 3. Payment History
        $paymentRes = $this->get(route('account.payment-history.index'));
        $paymentRes->assertOk()
            ->assertSee('Account Settings')
            ->assertSee(route('profile.edit'));

        // 4. Referrals
        $referralRes = $this->get(route('account.referrals'));
        $referralRes->assertOk()
            ->assertSee('Account Settings')
            ->assertSee(route('profile.edit'));
    }

    public function test_profile_update_validates_https_and_rejects_unsafe_urls(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Valid HTTPS URLs
        $validRes = $this->patch(route('profile.update'), [
            'name' => 'Valid User',
            'email' => $user->email,
            'github_url' => 'https://github.com/validuser',
            'portfolio_url' => 'https://validuser.dev/portfolio',
        ]);

        $validRes->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame('https://github.com/validuser', $user->github_url);
        $this->assertSame('https://validuser.dev/portfolio', $user->portfolio_url);

        // Reject non-HTTPS (http://)
        $httpRes = $this->patch(route('profile.update'), [
            'name' => 'Insecure User',
            'email' => $user->email,
            'github_url' => 'http://github.com/insecure',
        ]);
        $httpRes->assertSessionHasErrors(['github_url']);

        // Reject javascript: protocol
        $jsRes = $this->patch(route('profile.update'), [
            'name' => 'XSS User',
            'email' => $user->email,
            'portfolio_url' => 'javascript:alert(1)',
        ]);
        $jsRes->assertSessionHasErrors(['portfolio_url']);

        // Reject URLs with embedded credentials (e.g. https://user:pass@evil.com)
        $credRes = $this->patch(route('profile.update'), [
            'name' => 'Cred User',
            'email' => $user->email,
            'github_url' => 'https://user:secret@github.com/leak',
        ]);
        $credRes->assertSessionHasErrors(['github_url']);
    }

    public function test_valid_links_with_at_in_path_and_optional_null_persist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Valid User', 'email' => $user->email,
            'portfolio_url' => 'https://example.test/@developer?contact=person@example.test',
            'github_url' => null,
        ])->assertSessionHasNoErrors();
        $this->assertSame('https://example.test/@developer?contact=person@example.test', $user->fresh()->portfolio_url);
        $this->assertNull($user->fresh()->github_url);
    }

    public function test_unsafe_and_malformed_profile_links_keep_retry_page_renderable(): void
    {
        $user = User::factory()->create(['portfolio_url' => 'https://example.test/old']);
        $this->actingAs($user);
        foreach (["\nhttps://example.test", "https://example.test\t", 'https://example.test/%0Aunsafe', 'https://example.test/'.str_repeat('x', 2048), ['invalid']] as $url) {
            $this->from(route('profile.edit'))->patch(route('profile.update'), [
                'name' => 'Valid User', 'email' => $user->email, 'portfolio_url' => $url,
            ])->assertSessionHasErrors('portfolio_url');
            $this->get(route('profile.edit'))->assertOk();
            $this->assertSame('https://example.test/old', $user->fresh()->portfolio_url);
        }
    }

    public function test_security_errors_override_stale_section_and_native_query_links_select_section(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->from(route('profile.edit', ['section' => 'notifications']))
            ->put(route('password.update'), [
                'current_password' => 'incorrect', 'password' => 'updated-password', 'password_confirmation' => 'updated-password',
            ])->assertSessionHasErrorsIn('updatePassword', 'current_password');
        $page = $this->get(route('profile.edit', ['section' => 'notifications']))->assertOk();
        preg_match('/<div[^>]*id="security"[^>]*>/', $page->getContent(), $security);
        $this->assertNotEmpty($security);
        $this->assertStringNotContainsString(' hidden ', $security[0]);

        // A fresh request with no flashed error uses a direct query section without JS.
        $page = $this->get(route('profile.edit', ['section' => 'notifications']))->assertOk();
        preg_match('/<div[^>]*id="notifications"[^>]*>/', $page->getContent(), $notifications);
        $this->assertNotEmpty($notifications);
        $this->assertStringNotContainsString(' hidden ', $notifications[0]);
        $page->assertSee('accountSettings(', false)
            ->assertSee(route('profile.edit', ['section' => 'security']));
    }
}
