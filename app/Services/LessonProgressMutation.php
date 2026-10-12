<?php

namespace App\Services;

use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LessonProgressMutation
{
    /** @return array{progress: LessonProgress, summary: array{total: int, completed: int, percentage: int}} */
    public static function run(User $user, Community $community, Course $course, Lesson $lesson, bool $completed): array
    {
        // Match creator write order; no writes may use route-bound access snapshots.
        return DB::transaction(function () use ($user, $community, $course, $lesson, $completed) {
            $community = Community::query()->lockForUpdate()->findOrFail($community->id);
            $course = $community->courses()->lockForUpdate()->findOrFail($course->id);
            $section = $course->sections()->lockForUpdate()->findOrFail($lesson->course_section_id);
            $lesson = $section->lessons()->lockForUpdate()->findOrFail($lesson->id);
            $membership = $community->memberships()->where('user_id', $user->id)->lockForUpdate()->first();
            abort_unless($membership?->status === 'ACTIVE', 404);

            $course->setRelation('community', $community);
            $section->setRelation('course', $course);
            $lesson->setRelation('section', $section);
            Gate::forUser($user)->authorize('updateProgress', $lesson);

            $progress = $lesson->progresses()->where('user_id', $user->id)->lockForUpdate()->first()
                ?? new LessonProgress(['user_id' => $user->id, 'lesson_id' => $lesson->id]);
            $progress->completed_at = $completed ? ($progress->completed ? ($progress->completed_at ?? now()) : now()) : null;
            $progress->completed = $completed;
            if (! $progress->exists || $progress->isDirty()) {
                $progress->save();
            }

            return ['progress' => $progress, 'summary' => $course->progressSummaryFor($user)];
        }, 3);
    }
}
