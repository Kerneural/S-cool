<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_reset_password_link_fails_with_invalid_email_format(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'invalid-email-format']);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-reset-token-xyz',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_cannot_be_reset_with_unmatched_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'some-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'mismatched-password-456',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_cannot_be_reset_with_short_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'some-token',
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_old_password_is_rejected_and_new_password_authenticates_after_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('original-secret-password'),
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $resetResponse = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-updated-password-123',
                'password_confirmation' => 'new-updated-password-123',
            ]);

            $resetResponse
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            // Old password must fail
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'original-secret-password',
            ]);
            $this->assertGuest();

            // New password must succeed
            $loginResponse = $this->post('/login', [
                'email' => $user->email,
                'password' => 'new-updated-password-123',
            ]);
            $this->assertAuthenticated();
            $loginResponse->assertRedirect(route('dashboard', absolute: false));

            return true;
        });
    }

    public function test_password_reset_token_cannot_be_reused(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            // First reset: should succeed
            $firstResponse = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'first-new-password',
                'password_confirmation' => 'first-new-password',
            ]);
            $firstResponse->assertSessionHasNoErrors();

            // Second reset attempt with same token: must fail
            $secondResponse = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'second-new-password',
                'password_confirmation' => 'second-new-password',
            ]);
            $secondResponse->assertSessionHasErrors('email');

            return true;
        });
    }
}
