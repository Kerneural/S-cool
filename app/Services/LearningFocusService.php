<?php

namespace App\Services;

use App\DTOs\LearningFocusData;
use App\Models\Community;
use App\Models\Course;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\User;

class LearningFocusService
{
    /**
     * Resolve the current learning focus and upcoming event for a user in a community.
     */
    public static function resolve(Community $community, ?User $user): LearningFocusData
    {
        $community = $community->fresh();
        if (! $community || $user === null || ! $community->hasAccess($user)) {
            return new LearningFocusData(state: LearningFocusData::STATE_LOCKED);
        }

        $upcomingEventData = self::resolveUpcomingEvent($community, $user);

        // Check if community has any published courses
        $hasAnyPublishedCourses = $community->courses()->published()->exists();
        if (! $hasAnyPublishedCourses) {
            return new LearningFocusData(
                state: LearningFocusData::STATE_EMPTY,
                upcomingEvent: $upcomingEventData
            );
        }

        $isCreator = $community->isCreator($user);

        // Query accessible courses in deterministic order: course.order ASC, course.id ASC
        $accessibleCoursesQuery = $community->courses()
            ->select(['id', 'community_id', 'title', 'status', 'access_mode', 'order'])
            ->published()
            ->ordered()
            ->with([
                'sections' => fn ($q) => $q->select(['id', 'course_id', 'title', 'order'])->ordered()->with([
                    'lessons' => fn ($lq) => $lq->select(['id', 'course_section_id', 'title', 'status', 'order'])->published()->ordered()->with([
                        'progresses' => fn ($pq) => $pq->select(['id', 'lesson_id', 'user_id', 'completed'])->where('user_id', $user->id),
                    ]),
                ]),
            ]);

        if (! $isCreator) {
            $accessibleCoursesQuery->entitledTo($user);
        }

        $accessibleCourses = $accessibleCoursesQuery->get();

        if ($accessibleCourses->isEmpty()) {
            // Community has published courses, but member cannot access any of them
            return new LearningFocusData(
                state: LearningFocusData::STATE_LOCKED,
                upcomingEvent: $upcomingEventData
            );
        }

        $totalLessons = 0;
        $completedLessons = 0;
        /** @var Course|null $nextCourse */
        $nextCourse = null;
        /** @var Lesson|null $nextLesson */
        $nextLesson = null;

        // Traverse courses -> sections -> lessons in strict hierarchical order
        foreach ($accessibleCourses as $course) {
            foreach ($course->sections as $section) {
                foreach ($section->lessons as $lesson) {
                    $lesson->setRelation('section', $section);
                    $totalLessons++;
                    $isCompleted = $lesson->isCompletedBy($user);

                    if ($isCompleted) {
                        $completedLessons++;
                    } elseif ($nextLesson === null) {
                        $nextLesson = $lesson;
                        $nextCourse = $course;
                    }
                }
            }
        }

        if ($totalLessons === 0) {
            // Check if there are any published lessons anywhere in the community
            $hasAnyPublishedLessons = Lesson::whereHas('section.course', function ($q) use ($community): void {
                $q->where('community_id', $community->id)->published();
            })->published()->exists();

            return new LearningFocusData(
                state: $hasAnyPublishedLessons ? LearningFocusData::STATE_LOCKED : LearningFocusData::STATE_EMPTY,
                upcomingEvent: $upcomingEventData
            );
        }

        $percentage = (int) round(100 * $completedLessons / $totalLessons);
        $hasStarted = $completedLessons > 0;

        if ($nextLesson !== null) {
            return new LearningFocusData(
                state: LearningFocusData::STATE_CONTINUE,
                nextCourse: $nextCourse,
                nextLesson: $nextLesson,
                totalLessons: $totalLessons,
                completedLessons: $completedLessons,
                percentage: $percentage,
                hasStarted: $hasStarted,
                upcomingEvent: $upcomingEventData
            );
        }

        return new LearningFocusData(
            state: LearningFocusData::STATE_COMPLETE,
            nextCourse: null,
            nextLesson: null,
            totalLessons: $totalLessons,
            completedLessons: $completedLessons,
            percentage: 100,
            hasStarted: true,
            upcomingEvent: $upcomingEventData
        );
    }

    /**
     * Resolve the next scheduled upcoming non-cancelled event for a community.
     *
     * @return array{
     *     id: int,
     *     title: string,
     *     starts_at: string,
     *     formatted_time: string,
     *     timezone: string,
     *     meeting_url: ?string
     * }|null
     */
    protected static function resolveUpcomingEvent(Community $community, ?User $user): ?array
    {
        /** @var Event|null $event */
        $event = $community->events()
            ->where('status', 'SCHEDULED')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->orderBy('id')
            ->first();

        if (! $event) {
            return null;
        }

        $canViewMeeting = $user !== null
            && ($community->isCreator($user) || $community->isActiveMember($user))
            && $event->isScheduled()
            && ! empty($event->meeting_url);

        $localTime = $event->localStartsAt();

        return [
            'id' => $event->id,
            'title' => $event->title,
            'starts_at' => (string) $event->starts_at,
            'formatted_time' => $localTime->format('M j, Y \a\t g:i A').' ('.$event->timezone.')',
            'timezone' => $event->timezone,
            'meeting_url' => $canViewMeeting ? $event->meeting_url : null,
        ];
    }
}
