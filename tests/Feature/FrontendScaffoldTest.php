<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendScaffoldTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_layout_renders_correctly_with_vite_production_assets(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Verify HTML semantic structure and layout elements
        $response->assertSee('<meta name="csrf-token"', false);
        $response->assertSee('<title>S-cool</title>', false);
        $response->assertSee('Welcome back');
        $response->assertSee('S-cool');
        $response->assertSee(route('register'), false);
        $response->assertSee(route('password.request'), false);

        // Verify Vite production assets injected into guest head
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('.css', false);
        $response->assertSee('.js', false);

        // Verify form fields
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('type="submit"', false);
    }

    public function test_verified_invitee_receives_one_alpine_runtime_for_token_acceptance(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/invitations/999999');

        $response->assertOk();
        $response->assertSee('x-data="invitationAcceptance"', false);
        $response->assertSee('x-bind:disabled="!token"', false);
        $this->assertSame(1, substr_count($response->getContent(), '/livewire/livewire'));
    }

    public function test_app_layout_renders_correctly_with_navigation_and_header(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'layout-demo@scool.local',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Verify App layout elements
        $response->assertSee('<meta name="csrf-token"', false);
        $response->assertSee('<title>S-cool</title>', false);
        $response->assertSee('<main>', false);
        $response->assertSee('Communities');
        $response->assertSee('aria-label="S-cool home"', false);
        $response->assertSee('aria-controls="mobile-navigation"', false);

        // Verify user profile binding in navigation
        $response->assertSee('Demo User', false);

        // Verify Vite production assets
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('.css', false);
        $response->assertSee('.js', false);
    }

    public function test_alpine_directives_are_present_in_navigation_and_dropdown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Verify mobile navigation Alpine state
        $response->assertSee('x-data="{ open: false }"', false);
        $response->assertSee('@click="open = ! open"', false);

        // Verify dropdown Alpine state and click outside directive
        $response->assertSee('@click.outside="open = false"', false);
        $response->assertSee('x-show="open"', false);
        $response->assertSee('x-transition:enter=', false);
    }

    public function test_alpine_modal_component_renders_in_profile_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);

        // Verify Alpine modal bindings in delete user modal
        $response->assertSee('show: false,', false);
        $response->assertSee('x-show="show"', false);
        // Default callers close immediately; consumers may add a discard guard.
        // Every dismissal path must use the same hook rather than bypassing it.
        $response->assertSee('closeModal() { if (true) this.show = false }', false);
        $response->assertSee('x-on:close.stop="closeModal()"', false);
        $response->assertSee('x-on:keydown.escape.window="if (show) closeModal()"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('aria-labelledby="confirm-user-deletion-title"', false);
        $response->assertSee('returnFocus?.focus()', false);

        // The panel must sit above the fixed backdrop even without a transform.
        // Browser hit-testing is still required to verify actual pointer behavior.
        $response->assertSee('class="relative z-10 mb-6 bg-white', false);
    }
}
