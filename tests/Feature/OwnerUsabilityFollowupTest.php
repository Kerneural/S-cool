<?php

namespace Tests\Feature;

use App\Livewire\ConversationThread;
use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use App\Services\CommunityContributionService;
use App\Services\CommunityMessaging;
use App\Support\LessonMarkdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerUsabilityFollowupTest extends TestCase
{
    use RefreshDatabase;

    public function test_managed_images_are_private_draft_safe_and_render_in_preview_and_reader(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $course = Course::factory()->published()->included()->create(['community_id' => $community->id]);
        $member = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $uploadRoute = route('communities.courses.lesson-images.store', [$community, $course]);
        $url = $this->actingAs($community->creator)->postJson($uploadRoute, [
            'image' => UploadedFile::fake()->image('diagram.png'),
        ])->assertCreated()->json('url');
        $this->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs($member)->get($url)->assertNotFound();
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'DRAFT', 'content' => '![Sơ đồ]('.$url.')']);
        $this->get($url)->assertNotFound();
        $lesson->update(['status' => 'PUBLISHED']);
        $this->get($url)->assertOk();
        $this->get(route('communities.lessons.show', [$community, $course, $lesson]))->assertOk()->assertSee('src="'.$url.'"', false);
        $html = LessonMarkdown::toHtml('![Sơ đồ]('.$url.') ![Tracker](https://elsewhere.test/x.png)', $course);
        $this->assertSame(1, substr_count($html, '<img'));
        $this->assertStringContainsString('href="https://elsewhere.test/x.png"', $html);
        $this->postJson($uploadRoute, ['image' => UploadedFile::fake()->image('x.png')])->assertNotFound();
        $course->forceFill(['access_mode' => Course::ACCESS_MODE_ONE_TIME, 'price' => 100000])->save();
        $this->get($url)->assertNotFound();
        CourseEntitlement::create(['community_id' => $community->id, 'course_id' => $course->id, 'user_id' => $member->id]);
        $this->get($url)->assertOk();
        $this->actingAs(User::factory()->create())->get($url)->assertNotFound();
        $lesson->update(['status' => 'DRAFT']);
        $this->actingAs($member)->get($url)->assertNotFound();
    }

    public function test_image_upload_rejects_invalid_files_and_cross_community_routes(): void
    {
        Storage::fake('community_media');
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $course = Course::factory()->create(['community_id' => $community->id]);
        $this->actingAs($community->creator)->postJson(route('communities.courses.lesson-images.store', [$community, $course]), [
            'image' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml'),
        ])->assertUnprocessable();
        $this->postJson(route('communities.courses.lesson-images.store', [$community, $course]), [
            'image' => UploadedFile::fake()->image('large.png')->size(2049),
        ])->assertUnprocessable();
        $this->postJson(route('communities.courses.lesson-images.store', [$community, $course]), [
            'image' => UploadedFile::fake()->image('wide.png', 4097, 1),
        ])->assertUnprocessable();
        $other = Community::factory()->create(['creator_id' => $community->creator_id]);
        $this->postJson(route('communities.courses.lesson-images.store', [$other, $course]), [
            'image' => UploadedFile::fake()->image('x.png'),
        ])->assertNotFound();
    }

    public function test_live_conversation_sends_refreshes_without_losing_draft_and_rechecks_access(): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $member = User::factory()->create();
        $membership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        [$one, $two] = CommunityConversation::canonicalParticipants($community->creator_id, $member->id);
        $conversation = CommunityConversation::create(['community_id' => $community->id, 'user_one_id' => $one, 'user_two_id' => $two]);
        $thread = Livewire::actingAs($member)->test(ConversationThread::class, ['community' => $community, 'conversation' => $conversation]);
        $thread->set('body', 'Tin nhắn gửi ngay')->call('send')->assertHasNoErrors()->assertSet('body', '')->assertSee('Tin nhắn gửi ngay');
        $thread->set('body', 'Bản nháp chưa gửi');
        app(CommunityMessaging::class)->send($community, $conversation, $community->creator, 'Phản hồi mới', 'owner-reply');
        $thread->call('$refresh')->assertSee('Phản hồi mới')->assertSet('body', 'Bản nháp chưa gửi');
        $this->assertDatabaseCount('community_messages', 2);
        $membership->forceFill(['status' => 'SUSPENDED'])->save();
        $thread->call('$refresh')->assertNotFound();
    }

    public function test_vietnamese_pagination_cover_and_year_calendar(): void
    {
        app()->setLocale('vi');
        $this->assertSame('Hiển thị', __('Showing'));
        $this->assertSame('kết quả', __('results'));
        $community = Community::factory()->create(['status' => 'ACTIVE', 'cover_path' => 'cover.png']);
        $response = $this->actingAs($community->creator)->get(route('communities.show', $community))->assertOk();
        $response->assertSee(route('communities.cover.show', $community), false);
        $heatmap = app(CommunityContributionService::class)->getActivityHeatmap($community, $community->creator);
        $this->assertCount(365, $heatmap['days']);
        $this->assertStringContainsString('365 ngày', $heatmap['accessible_summary']);
    }
}
