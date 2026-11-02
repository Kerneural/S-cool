<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementUsabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_forms_keep_their_own_payloads_and_validation_retains_entered_text(): void
    {
        app()->setLocale('vi');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $url = route('communities.edit', $community);
        $response = $this->actingAs($community->creator)->get($url)->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $form = $xpath->query('//form[@id="community-settings-form"]')->item(0);
        $this->assertNotNull($form);
        $this->assertSame(route('communities.update', $community), $form->getAttribute('action'));
        foreach (['name', 'description', 'rules', 'intro_video_url', 'onboarding_enabled', 'access_mode', 'billing_period', 'price_vnd', 'referral_commission_rate'] as $name) {
            $this->assertSame(1, $xpath->query('.//*[@name="'.$name.'"]', $form)->length, $name);
        }
        $this->assertSame(0, $xpath->query('.//input[@type="file"]', $form)->length);
        $this->assertSame(1, $xpath->query('//form[@enctype="multipart/form-data"]//input[@name="cover"]')->length);
        $this->from($url)->put(route('communities.update', $community), [
            'name' => '', 'description' => 'Nội dung đang sửa cần giữ lại', 'access_mode' => 'FREE', 'billing_period' => 'ONE_TIME',
        ])->assertRedirect($url)->assertSessionHasErrors('name');
        $this->get($url)->assertOk()->assertSee('Nội dung đang sửa cần giữ lại');
        $this->assertNotSame('Nội dung đang sửa cần giữ lại', $community->fresh()->description);
    }

    public function test_management_courses_entry_is_scoped_and_members_cannot_enable_it_with_a_query(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $course = Course::factory()->published()->included()->create(['community_id' => $community->id]);
        $courses = route('communities.classroom.index', [$community, 'manage' => 1]);
        $this->actingAs($community->creator)->get($courses)->assertOk()
            ->assertSee('id="creator-navigation"', false)->assertSee('aria-current="page"', false)
            ->assertSee(route('communities.manage.courses.show', [$community, $course]), false);
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $this->actingAs($member)->get($courses)->assertOk()->assertDontSee('id="creator-navigation"', false);
        $this->get(route('communities.edit', $community))->assertNotFound();
        $this->get(route('communities.invitations.index', $community))->assertNotFound();
    }

    public function test_post_and_comment_actions_have_context_and_retain_authorization(): void
    {
        app()->setLocale('vi');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $community->creator_id]);
        Comment::factory()->create(['post_id' => $post->id, 'user_id' => $community->creator_id]);
        $url = route('communities.posts.show', [$community, $post]);
        $this->actingAs($community->creator)->get($url)->assertOk()->assertSee('Xóa bài viết')->assertSee('Xóa bình luận');
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $this->actingAs($member)->get($url)->assertOk()->assertDontSee('Xóa bài viết')->assertDontSee('Xóa bình luận');
    }
}
