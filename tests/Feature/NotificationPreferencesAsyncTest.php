<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesAsyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_async_toggle_updates_single_preference_and_returns_server_ack(): void
    {
        $user = User::factory()->create([
            'notify_replies' => true,
            'notify_courses' => true,
        ]);

        $this->actingAs($user);

        // 1. Mutate only notify_replies -> false
        $response1 = $this->patchJson(route('profile.notifications.update'), [
            'notify_replies' => false,
        ]);

        $response1->assertOk()
            ->assertJson([
                'status' => 'success',
                'notify_replies' => false,
                'notify_courses' => true, // Untouched preference preserved
            ]);

        $user->refresh();
        $this->assertFalse($user->notify_replies);
        $this->assertTrue($user->notify_courses);

        // 2. Mutate only notify_courses -> false
        $response2 = $this->patchJson(route('profile.notifications.update'), [
            'notify_courses' => false,
        ]);

        $response2->assertOk()
            ->assertJson([
                'status' => 'success',
                'notify_replies' => false, // Previously mutated preference preserved
                'notify_courses' => false,
            ]);

        $user->refresh();
        $this->assertFalse($user->notify_replies);
        $this->assertFalse($user->notify_courses);
    }

    public function test_async_toggle_rejects_non_boolean_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patchJson(route('profile.notifications.update'), [
            'notify_replies' => 'not-a-boolean',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notify_replies']);
    }

    public function test_unknown_fields_and_empty_json_are_rejected_without_mutation(): void
    {
        $user = User::factory()->create(['notify_replies' => true]);
        $other = User::factory()->create(['notify_replies' => true]);
        $this->actingAs($user);

        foreach ([[], ['other' => false], ['user_id' => $other->id, 'notify_replies' => false]] as $payload) {
            $this->patchJson(route('profile.notifications.update'), $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('preferences');
        }
        $this->assertTrue($user->fresh()->notify_replies);
        $this->assertTrue($other->fresh()->notify_replies);
    }

    public function test_native_form_can_disable_both_preferences_and_preserves_notification_section(): void
    {
        $user = User::factory()->create(['notify_replies' => true, 'notify_courses' => true]);
        $this->actingAs($user)->get(route('profile.edit', ['section' => 'notifications']))
            ->assertOk()->assertSee('type="hidden" name="notify_replies" value="0"', false)
            ->assertSee('type="hidden" name="notify_courses" value="0"', false);
        $this->patch(route('profile.notifications.update'), ['notify_replies' => '0', 'notify_courses' => '0'])
            ->assertRedirect(route('profile.edit', ['section' => 'notifications']))
            ->assertSessionHas('status', 'notification-preferences-updated');
        $this->assertFalse($user->fresh()->notify_replies);
        $this->assertFalse($user->fresh()->notify_courses);
    }

    public function test_guest_cannot_change_preferences(): void
    {
        $this->patchJson(route('profile.notifications.update'), ['notify_replies' => false])->assertUnauthorized();
    }
}
