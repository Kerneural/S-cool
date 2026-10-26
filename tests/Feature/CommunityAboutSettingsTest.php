<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityAboutSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_update_rules_and_intro_video_and_member_can_view_escaped_rules_and_video(): void
    {
        $creator = User::factory()->create([
            'name' => 'Prof Alice',
            'email' => 'alice@secret.org',
        ]);
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Math Guild',
            'slug' => 'math-guild',
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create([
            'name' => 'Bob Student',
            'email' => 'bob@secret.org',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator);

        $rulesText = "Rule 1: Be respectful.\nRule 2: No spam.<script>alert('xss')</script>";
        $validVideoUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

        // 1. Creator updates rules and intro video URL
        $updateResponse = $this->put(route('communities.update', $community), [
            'name' => 'Math Guild',
            'description' => 'A rigorous math study group',
            'rules' => $rulesText,
            'intro_video_url' => $validVideoUrl,
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $updateResponse->assertRedirect(route('communities.show', $community));

        $community->refresh();
        $this->assertSame($rulesText, $community->rules);
        $this->assertSame($validVideoUrl, $community->intro_video_url);

        // 2. Member views About page
        $this->actingAs($member);

        $aboutResponse = $this->get(route('communities.about', $community));
        $aboutResponse->assertOk()
            ->assertSee('About this Community')
            ->assertSee('A rigorous math study group')
            ->assertSee('Rule 1: Be respectful.')
            ->assertSee('Rule 2: No spam.')
            ->assertDontSee("<script>alert('xss')</script>", false) // Escaped
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ')
            ->assertSee('Prof Alice')
            ->assertDontSee('alice@secret.org') // Non-disclosure of email
            ->assertSee('Active members')
            ->assertSee('1'); // Exactly 1 active member

        // Direct show route also serves About info
        $showResponse = $this->get(route('communities.show', $community));
        $showResponse->assertOk()
            ->assertSee('Rule 1: Be respectful.');
    }

    public function test_outsider_and_removed_member_cannot_read_about_page_and_receive_404(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'rules' => 'Secret member rules',
            'intro_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $removedMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $removedMember->id,
            'status' => 'REMOVED',
        ]);

        $outsider = User::factory()->create();

        // 1. Guest -> redirect to login
        $this->get(route('communities.about', $community))
            ->assertRedirect(route('login'));

        // 2. Outsider sees the marketing preview, never the protected workspace.
        $this->actingAs($outsider);
        $this->get(route('communities.about', $community))->assertOk()->assertViewIs('communities.preview');
        $this->get(route('communities.show', $community))->assertNotFound();

        // 3. Removed member may read marketing metadata but cannot join or read content.
        $this->actingAs($removedMember);
        $this->get(route('communities.about', $community))->assertOk()->assertViewIs('communities.preview')->assertDontSee('Join community');
        $this->get(route('communities.show', $community))->assertNotFound();
    }

    public function test_validation_rejects_invalid_video_urls_and_overlong_rules(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'rules' => 'Initial safe rules',
        ]);

        $this->actingAs($creator);

        // 1. Overlong rules > 5000 characters
        $oversizeRules = str_repeat('a', 5001);
        $overlongResponse = $this->put(route('communities.update', $community), [
            'name' => $community->name,
            'rules' => $oversizeRules,
        ]);
        $overlongResponse->assertSessionHasErrors('rules');

        // 2. Invalid video URL domain (not YouTube or Vimeo)
        $invalidUrlResponse = $this->put(route('communities.update', $community), [
            'name' => $community->name,
            'intro_video_url' => 'https://malicious-site.com/video.mp4',
        ]);
        $invalidUrlResponse->assertSessionHasErrors('intro_video_url');

        // 3. Raw HTML/iframe in video URL
        $rawHtmlUrlResponse = $this->put(route('communities.update', $community), [
            'name' => $community->name,
            'intro_video_url' => '<iframe src="evil"></iframe>',
        ]);
        $rawHtmlUrlResponse->assertSessionHasErrors('intro_video_url');

        // Database remains unmutated
        $community->refresh();
        $this->assertSame('Initial safe rules', $community->rules);
        $this->assertNull($community->intro_video_url);
    }

    public function test_empty_states_render_gracefully_without_invented_metrics(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
            'description' => null,
            'rules' => null,
            'intro_video_url' => null,
        ]);

        $this->actingAs($creator);

        $response = $this->get(route('communities.about', $community));
        $response->assertOk()
            ->assertSee('No description provided yet.')
            ->assertSee('No community rules posted yet.')
            ->assertDontSee('iframe')
            ->assertSee('Active members')
            ->assertSee('0'); // 0 active memberships
    }

    public function test_active_member_counts_exclude_suspended_and_removed_members(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        // 2 active members
        $m1 = User::factory()->create();
        $m2 = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $m1->id, 'status' => 'ACTIVE']);
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $m2->id, 'status' => 'ACTIVE']);

        // 1 suspended member
        $suspended = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $suspended->id, 'status' => 'SUSPENDED']);

        // 1 removed member
        $removed = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $removed->id, 'status' => 'REMOVED']);

        $this->actingAs($creator);

        $response = $this->get(route('communities.about', $community));
        $response->assertOk()
            ->assertSee('Active members')
            ->assertSee('2'); // Strictly 2, excluding suspended and removed
    }
}
