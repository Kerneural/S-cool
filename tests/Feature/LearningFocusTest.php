<?php

namespace Tests\Feature;

use App\DTOs\LearningFocusData;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\LearningFocusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningFocusTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_focus_is_empty_when_no_published_courses_exist(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        $focus = LearningFocusService::resolve($community, $user);

        $this->assertSame(LearningFocusData::STATE_EMPTY, $focus->state);
        $this->assertTrue($focus->isEmpty());
        $this->assertNull($focus->nextCourse);
        $this->assertNull($focus->nextLesson);
        $this->assertSame(0, $focus->totalLessons);
        $this->assertSame(0, $focus->completedLessons);
        $this->assertSame(0, $focus->percentage);
    }

    public function test_learning_focus_is_locked_when_user_is_not_active_member(): void
    {
        $community = Community::factory()->create();
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        $visitor = User::factory()->create();

        $focus = LearningFocusService::resolve($community, $visitor);

        $this->assertSame(LearningFocusData::STATE_LOCKED, $focus->state);
        $this->assertTrue($focus->isLocked());
    }

    public function test_learning_focus_is_locked_when_all_courses_are_paid_and_unentitled(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        $paidCourse = Course::factory()->published()->create([
            'community_id' => $community->id,
            'access_mode' => Course::ACCESS_MODE_ONE_TIME,
            'price' => 500000,
        ]);
        $section = CourseSection::factory()->create(['course_id' => $paidCourse->id]);
        Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        $focus = LearningFocusService::resolve($community, $user);

        $this->assertSame(LearningFocusData::STATE_LOCKED, $focus->state);
        $this->assertTrue($focus->isLocked());
        $this->assertNull($focus->nextLesson);
    }

    public function test_learning_focus_continue_determines_next_lesson_in_strict_deterministic_order(): void
    {
        $community = Community::factory()->create();
        $user = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'ACTIVE',
        ]);

        // Create Course 2 (order 2) and Course 1 (order 1)
        $course2 = Course::factory()->published()->create([
            'community_id' => $community->id,
            'order' => 2,
            'title' => 'Course 2',
        ]);
        $c2Sec = CourseSection::factory()->create(['course_id' => $course2->id, 'order' => 1]);
        $c2L1 = Lesson::factory()->published()->create(['course_section_id' => $c2Sec->id, 'order' => 1, 'title' => 'C2 L1']);

        $course1 = Course::factory()->published()->create([
            'community_id' => $community->id,
            'order' => 1,
            'title' => 'Course 1',
        ]);
        $c1Sec1 = CourseSection::factory()->create(['course_id' => $course1->id, 'order' => 1]);
        $c1Sec2 = CourseSection::factory()->create(['course_id' => $course1->id, 'order' => 2]);

        $c1L1 = Lesson::factory()->published()->create(['course_section_id' => $c1Sec1->id, 'order' => 1, 'title' => 'C1 S1 L1']);
        $c1L2 = Lesson::factory()->published()->create(['course_section_id' => $c1Sec1->id, 'order' => 2, 'title' => 'C1 S1 L2']);
        $c1L3 = Lesson::factory()->published()->create(['course_section_id' => $c1Sec2->id, 'order' => 1, 'title' => 'C1 S2 L1']);

        // 1. Initial state: First lesson should be C1 S1 L1
        $focus = LearningFocusService::resolve($community, $user);
        $this->assertSame(LearningFocusData::STATE_CONTINUE, $focus->state);
        $this->assertSame($c1L1->id, $focus->nextLesson?->id);
        $this->assertSame($course1->id, $focus->nextCourse?->id);
        $this->assertSame(4, $focus->totalLessons);
        $this->assertSame(0, $focus->completedLessons);
        $this->assertSame(0, $focus->percentage);
        $this->assertFalse($focus->hasStarted);

        // 2. Complete C1 S1 L1: Next should be C1 S1 L2
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $c1L1->id, 'completed' => true]);
        $focus = LearningFocusService::resolve($community, $user);
        $this->assertSame($c1L2->id, $focus->nextLesson?->id);
        $this->assertSame(1, $focus->completedLessons);
        $this->assertSame(25, $focus->percentage);
        $this->assertTrue($focus->hasStarted);

        // 3. Complete C1 S1 L2: Next should advance to C1 S2 L1
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $c1L2->id, 'completed' => true]);
        $focus = LearningFocusService::resolve($community, $user);
        $this->assertSame($c1L3->id, $focus->nextLesson?->id);
        $this->assertSame(2, $focus->completedLessons);
        $this->assertSame(50, $focus->percentage);

        // 4. Complete C1 S2 L1: Next should advance to Course 2 -> C2 L1
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $c1L3->id, 'completed' => true]);
        $focus = LearningFocusService::resolve($community, $user);
        $this->assertSame($c2L1->id, $focus->nextLesson?->id);
        $this->assertSame($course2->id, $focus->nextCourse?->id);
        $this->assertSame(3, $focus->completedLessons);
        $this->assertSame(75, $focus->percentage);

        // 5. Complete C2 L1: All completed! State becomes COMPLETE
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $c2L1->id, 'completed' => true]);
        $focus = LearningFocusService::resolve($community, $user);
        $this->assertSame(LearningFocusData::STATE_COMPLETE, $focus->state);
        $this->assertTrue($focus->isComplete());
        $this->assertNull($focus->nextLesson);
        $this->assertSame(4, $focus->completedLessons);
        $this->assertSame(100, $focus->percentage);
    }

    public function test_learning_focus_upcoming_event_gates_meeting_url_and_formats_timezone(): void
    {
        $community = Community::factory()->create();
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);
        $visitor = User::factory()->create();

        $event = Event::factory()->create([
            'community_id' => $community->id,
            'title' => 'Live Q&A Workshop',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(1),
            'timezone' => 'Asia/Ho_Chi_Minh',
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'status' => 'SCHEDULED',
        ]);

        // Active member sees meeting URL
        $memberFocus = LearningFocusService::resolve($community, $member);
        $this->assertNotNull($memberFocus->upcomingEvent);
        $this->assertSame($event->title, $memberFocus->upcomingEvent['title']);
        $this->assertSame('Asia/Ho_Chi_Minh', $memberFocus->upcomingEvent['timezone']);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $memberFocus->upcomingEvent['meeting_url']);

        // Creator sees meeting URL
        $creatorFocus = LearningFocusService::resolve($community, $community->creator);
        $this->assertNotNull($creatorFocus->upcomingEvent);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $creatorFocus->upcomingEvent['meeting_url']);

        // Visitors receive no private event metadata.
        $visitorFocus = LearningFocusService::resolve($community, $visitor);
        $this->assertNull($visitorFocus->upcomingEvent);

        // Cancelled event is not returned
        $event->update(['status' => 'CANCELLED']);
        $afterCancelFocus = LearningFocusService::resolve($community, $member);
        $this->assertNull($afterCancelFocus->upcomingEvent);
    }

    public function test_classroom_index_view_renders_learning_focus_elements(): void
    {
        $community = Community::factory()->create();
        $member = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $course = Course::factory()->published()->create([
            'community_id' => $community->id,
            'title' => 'Mastering Laravel 12',
        ]);
        $section = CourseSection::factory()->create(['course_id' => $course->id, 'title' => 'Section 1']);
        $lesson = Lesson::factory()->published()->create([
            'course_section_id' => $section->id,
            'title' => 'Deep Dive into Services',
        ]);

        $this->actingAs($member);

        $response = $this->get(route('communities.classroom.index', $community));
        $response->assertOk();
        $response->assertSee('Next Learning Step');
        $response->assertSee('Deep Dive into Services');
        $response->assertSee('Start Lesson');
        $response->assertSee('0%');

        // Complete lesson
        LessonProgress::create(['user_id' => $member->id, 'lesson_id' => $lesson->id, 'completed' => true]);

        $response = $this->get(route('communities.classroom.index', $community));
        $response->assertOk();
        $response->assertSee('All Caught Up!');
        $response->assertSee('100%');
    }

    public function test_community_entry_shows_learning_focus_and_empty_state_with_upcoming_event(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        $course = Course::factory()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id]);

        $this->actingAs($member)->get(route('communities.posts.index', $community))
            ->assertOk()->assertSee('Learning Focus')->assertSee($lesson->title)
            ->assertSee(route('communities.lessons.show', [$community, $course, $lesson]));

        $course->update(['status' => 'DRAFT']);
        $event = Event::factory()->create(['community_id' => $community->id, 'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour()]);
        foreach (['communities.posts.index', 'communities.classroom.index'] as $route) {
            $this->get(route($route, $community))->assertOk()->assertSee('No learning content yet')
                ->assertSee($event->title)->assertDontSee($lesson->title);
        }
    }

    public function test_focus_does_not_load_lesson_bodies_and_uses_id_ties_across_the_whole_catalog(): void
    {
        $community = Community::factory()->create();
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id])->user;
        Course::factory()->count(12)->published()->create(['community_id' => $community->id, 'order' => 0]);
        $course = Course::factory()->published()->create(['community_id' => $community->id, 'order' => 1]);
        $section = CourseSection::factory()->create(['course_id' => $course->id, 'order' => 0]);
        $first = Lesson::factory()->published()->create(['course_section_id' => $section->id, 'order' => 0]);
        Lesson::factory()->published()->create(['course_section_id' => $section->id, 'order' => 0]);
        $focus = LearningFocusService::resolve($community, $member);
        $this->assertSame($first->id, $focus->nextLesson?->id);
        $this->assertArrayNotHasKey('content', $focus->nextLesson->getAttributes());
        $this->assertArrayNotHasKey('video_url', $focus->nextLesson->getAttributes());
    }
}
