<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProofOfWork;
use App\Models\Post;
use App\Models\User;
use App\Services\CommunityContributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContributionPrivacyRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $member = User::factory()->create();
        $membership = CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'ACTIVE']);
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id, 'is_practical' => true]);
        $post = Post::factory()->create(['community_id' => $community->id, 'user_id' => $creator->id, 'course_id' => $course->id, 'lesson_id' => $lesson->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $member->id]);
        $post->update(['accepted_comment_id' => $comment->id]);

        return compact('creator', 'community', 'member', 'membership', 'course', 'section', 'lesson', 'post', 'comment');
    }

    public function test_helpful_profile_and_ranking_exclude_ineligible_retained_sources(): void
    {
        extract($this->fixture());
        $service = app(CommunityContributionService::class);
        $this->assertSame(1, $service->getHelpfulAnswersCount($community, $member));
        $this->actingAs($creator);
        $score = fn () => $this->get(route('communities.leaderboard', [$community, 'metric' => 'helpful_answers']))
            ->assertOk()->viewData('leaderboard')->getCollection()->firstWhere('user.id', $member->id)->points;
        $this->assertSame(1, $score());

        foreach ([[$course, 'status', 'DRAFT', 'PUBLISHED'], [$lesson, 'status', 'DRAFT', 'PUBLISHED'],
            [$membership, 'status', 'REMOVED', 'ACTIVE']] as [$model, $field, $invalid, $valid]) {
            $model->forceFill([$field => $invalid])->save();
            $this->assertSame(0, $service->getHelpfulAnswersCount($community, $member));
            if ($model !== $membership) {
                $this->assertSame(0, $score());
            }
            $model->forceFill([$field => $valid])->save();
        }

        $course->update(['access_mode' => Course::ACCESS_MODE_ONE_TIME, 'price' => 100000]);
        $this->assertSame(0, $service->getHelpfulAnswersCount($community, $member));
        $this->assertSame(0, $score());
        $course->update(['access_mode' => Course::ACCESS_MODE_INCLUDED]);

        $otherPost = Post::factory()->create(['community_id' => $community->id]);
        $comment->update(['post_id' => $otherPost->id]);
        $this->assertSame(0, $service->getHelpfulAnswersCount($community, $member));
        $comment->update(['post_id' => $post->id]);
        $comment->delete();
        $this->assertSame(0, $service->getHelpfulAnswersCount($community, $member));
        $comment->restore();
        $post->delete();
        $this->assertSame(0, $service->getHelpfulAnswersCount($community, $member));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'accepted_comment_id' => $comment->id]);
    }

    public function test_profile_has_daily_alternative_visible_history_and_viewer_scoped_provenance(): void
    {
        extract($this->fixture());
        $this->travelTo(now('UTC')->setTime(12, 0));
        $viewer = User::factory()->create();
        CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $viewer->id, 'status' => 'ACTIVE']);
        $course->update(['access_mode' => Course::ACCESS_MODE_ONE_TIME, 'price' => 100000, 'title' => 'PRIVATE-COURSE-CONTEXT']);
        // Entitlement is retained source data; use the existing model factory contracts.
        CourseEntitlement::create(['community_id' => $community->id, 'course_id' => $course->id, 'user_id' => $member->id]);
        LessonProofOfWork::create(['community_id' => $community->id, 'course_id' => $course->id,
            'lesson_id' => $lesson->id, 'user_id' => $member->id, 'status' => 'VERIFIED', 'is_current' => true,
            'skill_label_snapshot' => 'Safe DNS skill', 'summary' => 'PRIVATE-EVIDENCE-SUMMARY',
            'url' => 'https://example.com/private-evidence', 'reviewed_at' => now(), 'reviewer_id' => $creator->id]);
        Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id,
            'title' => 'Visible authored activity', 'created_at' => now()]);
        Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id,
            'title' => 'HIDDEN-COURSE-ACTIVITY', 'course_id' => $course->id, 'created_at' => now()]);
        Post::factory()->create(['community_id' => $community->id, 'user_id' => $member->id,
            'title' => 'OUTSIDE-WINDOW', 'created_at' => now()->subDays(90)]);

        $response = $this->actingAs($viewer)->get(route('communities.directory.show', [$community, $member]));
        $response->assertOk()->assertSee('Daily activity counts (UTC)')->assertSee('Recent community activity')
            ->assertSee('Visible authored activity')->assertSee('Safe DNS skill')->assertSee('1 Verified Work')
            ->assertDontSee('PRIVATE-COURSE-CONTEXT')->assertDontSee('HIDDEN-COURSE-ACTIVITY')
            ->assertDontSee('OUTSIDE-WINDOW')
            ->assertDontSee('PRIVATE-EVIDENCE-SUMMARY')->assertDontSee('https://example.com/private-evidence');
        $heatmap = $response->viewData('activityHeatmap');
        $this->assertCount(90, $heatmap['days']);
        $this->assertSame(1, $heatmap['total_posts']);
        $this->assertSame(0, $heatmap['total_comments']);
        $this->assertNull($response->viewData('verifiedSkills')['evidence'][0]['url']);

        $this->actingAs($creator)->get(route('communities.directory.show', [$community, $member]))
            ->assertOk()->assertSee('PRIVATE-COURSE-CONTEXT')->assertDontSee('PRIVATE-EVIDENCE-SUMMARY');
    }

    public function test_verified_ranking_is_bounded_and_does_not_create_cyclic_user_models(): void
    {
        extract($this->fixture());
        $this->actingAs($member);
        DB::enableQueryLog();
        $first = $this->get(route('communities.leaderboard', [$community, 'metric' => 'verified_skills']))->assertOk();
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        foreach (User::factory()->count(15)->create() as $extra) {
            CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $extra->id, 'status' => 'ACTIVE']);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $second = $this->get(route('communities.leaderboard', [$community, 'metric' => 'verified_skills']))->assertOk();
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual($smallCount + 1, $largeCount);
        $entry = $second->viewData('leaderboard')->getCollection()->first();
        $this->assertNotSame($entry, $entry->user);
        $this->assertArrayNotHasKey('user', $entry->user->getAttributes());
    }

    public function test_verified_work_count_is_not_distinct_labels_and_lost_access_suppresses_retained_records(): void
    {
        extract($this->fixture());
        foreach (['VERIFIED', 'VERIFIED', 'WAITING', 'REJECTED'] as $index => $status) {
            $source = Lesson::factory()->published()->create(['course_section_id' => $section->id, 'is_practical' => true]);
            LessonProofOfWork::create(['community_id' => $community->id, 'course_id' => $course->id,
                'lesson_id' => $source->id, 'user_id' => $member->id, 'status' => $status,
                'is_current' => true, 'skill_label_snapshot' => 'Shared label', 'reviewed_at' => now()]);
        }
        $service = app(CommunityContributionService::class);
        $summary = $service->getVerifiedSkills($community, $member, $creator);
        $this->assertSame(2, $summary['count']);
        $this->assertSame(1, $summary['distinct_count']);
        $membership->forceFill(['status' => 'REMOVED'])->save();
        $this->assertSame(0, $service->getVerifiedSkills($community, $member, $creator)['count']);
        $this->assertDatabaseCount('lesson_proof_of_works', 4);
        $this->actingAs($creator)->get(route('communities.directory.show', [$community, $member]))->assertNotFound();
    }

    public function test_feed_mini_ranking_uses_lifetime_metric_and_is_independent_of_feed_pagination(): void
    {
        extract($this->fixture());
        foreach (User::factory()->count(4)->create() as $extra) {
            CommunityMembership::factory()->create(['community_id' => $community->id, 'user_id' => $extra->id, 'status' => 'ACTIVE']);
        }
        $service = app(CommunityContributionService::class);
        $entries = $service->miniRanking($community);
        $this->assertCount(3, $entries);
        $this->assertSame([1, 2, 3], array_column($entries, 'rank'));
        $this->assertSame(['id', 'name', 'rank', 'points'], array_keys($entries[0]));
        $this->actingAs($member)->get(route('communities.posts.index', [$community, 'page' => 2]))
            ->assertOk()->assertSee('Engagement points · Lifetime')->assertSee('View all rankings')
            ->assertViewHas('miniRanking', $entries);
    }
}
