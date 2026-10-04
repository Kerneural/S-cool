<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MailpitPasswordResetIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure live SMTP to dispatch real emails directly to Mailpit
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'mailpit',
            'mail.mailers.smtp.port' => 1025,
            'mail.mailers.smtp.encryption' => null,
            'mail.mailers.smtp.username' => null,
            'mail.mailers.smtp.password' => null,
            'mail.from.address' => 'no-reply@scool.local',
            'mail.from.name' => 'S-cool',
        ]);
    }

    public function test_full_password_reset_flow_via_live_mailpit_smtp(): void
    {
        $mailpitBaseUrl = 'http://mailpit:8025';

        // Check if Mailpit is accessible from the container
        try {
            $ping = Http::timeout(3)->get("{$mailpitBaseUrl}/api/v1/messages");
            if (! $ping->successful()) {
                $this->markTestSkipped('Mailpit service is not reachable on http://mailpit:8025');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Mailpit service connection failed: '.$e->getMessage());
        }

        // 1. Create user with known original password
        $user = User::factory()->create([
            // Each run owns one recipient; never clear the shared Mailpit inbox.
            'email' => 'reset-'.Str::uuid().'@scool.local',
            'password' => Hash::make('original-devops-pwd-2026'),
        ]);

        // 2. Request password reset link (dispatches real SMTP message to Mailpit)
        $forgotResponse = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $forgotResponse->assertSessionHasNoErrors();

        // 3. Query Mailpit API to verify SMTP message was received
        $messagesResponse = Http::timeout(5)->get("{$mailpitBaseUrl}/api/v1/search", [
            'query' => 'to:'.$user->email,
        ]);
        $this->assertTrue($messagesResponse->successful());

        $messages = $messagesResponse->json('messages');
        $this->assertNotEmpty($messages, 'Mailpit did not receive any email from SMTP delivery.');

        $targetMessage = collect($messages)->first(function ($msg) use ($user) {
            $recipients = collect($msg['To'] ?? [])->pluck('Address')->all();

            return in_array($user->email, $recipients);
        });

        $this->assertNotNull($targetMessage, "No email in Mailpit was addressed to {$user->email}");

        // 4. Fetch the full message content and extract reset token / link
        $messageDetail = Http::get("{$mailpitBaseUrl}/api/v1/message/{$targetMessage['ID']}")->json();
        $emailContent = ($messageDetail['Text'] ?? '').' '.($messageDetail['HTML'] ?? '');

        preg_match('/reset-password\/([a-zA-Z0-9]+)\?email=([^"\'\s&]+)/', $emailContent, $matches);

        $this->assertCount(3, $matches, 'Failed to extract reset-password token and email from Mailpit email body.');
        $token = $matches[1];
        $encodedEmail = $matches[2];

        // 5. Render reset password screen with extracted token
        $renderResponse = $this->get("/reset-password/{$token}?email={$encodedEmail}");
        $renderResponse->assertStatus(200);

        // 6. Submit new password
        $resetSubmitResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'brand-new-secure-pass-2026',
            'password_confirmation' => 'brand-new-secure-pass-2026',
        ]);

        $resetSubmitResponse
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        // 7. Verify OLD password is now strictly rejected
        $failedLoginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'original-devops-pwd-2026',
        ]);
        $failedLoginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // 8. Verify NEW password authenticates successfully
        $successLoginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'brand-new-secure-pass-2026',
        ]);
        $this->assertAuthenticated();
        $successLoginResponse->assertRedirect(route('dashboard', absolute: false));
    }
}
