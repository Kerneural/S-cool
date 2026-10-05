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
        $response->assertSee('min-h-screen flex flex-col sm:justify-center items-center', false);
        $response->assertSee('w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg', false);

        // Verify Vite production assets injected into guest head
        $response->assertSee('/build/assets/app-', false);
        $response->assertSee('.css', false);
        $response->assertSee('.js', false);

        // Verify form fields
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('type="submit"', false);
    }

    public function test_app_layout_renders_correctly_with_navigation_and_header(): void
    {
        $user = User::factory()->create([
            'name' => 'Hoang DevOps Lead',
            'email' => 'hoang-lead@scool.local',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);

        // Verify App layout elements
        $response->assertSee('<meta name="csrf-token"', false);
        $response->assertSee('<title>S-cool</title>', false);
        $response->assertSee('min-h-screen bg-gray-100', false);
        $response->assertSee('<main>', false);
        $response->assertSee('logged in!', false);

        // Verify user profile binding in navigation
        $response->assertSee('Hoang DevOps Lead', false);

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
        $response->assertSee('x-on:close.stop="show = false"', false);
        $response->assertSee('x-on:keydown.escape.window="show = false"', false);
    }
}
