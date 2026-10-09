<?php

namespace App\Services;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use Closure;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ClassroomMutation
{
    // Serialize creator writes: community -> course -> section -> lesson.
    // Route-bound snapshots are not a current authorization or sibling-set check.
    public static function run(Community $community, ?Course $course, ?CourseSection $section, ?Lesson $lesson, Closure $write): mixed
    {
        return DB::transaction(function () use ($community, $course, $section, $lesson, $write) {
            $community = Community::query()->lockForUpdate()->findOrFail($community->id);
            Gate::authorize('update', $community);
            $course = $course ? $community->courses()->lockForUpdate()->findOrFail($course->id) : null;
            $course?->setRelation('community', $community);
            $section = $section ? $course->sections()->lockForUpdate()->findOrFail($section->id) : null;
            $section?->setRelation('course', $course);
            $lesson = $lesson ? $section->lessons()->lockForUpdate()->findOrFail($lesson->id) : null;
            $lesson?->setRelation('section', $section);

            return $write($community, $course, $section, $lesson);
        }, 3);
    }

    public static function reorder(HasMany $siblings, array $submitted): void
    {
        $existing = $siblings->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $submitted = array_values(array_map('intval', $submitted));
        if (count($submitted) !== count(array_unique($submitted))
            || count($submitted) !== count($existing)
            || array_diff($submitted, $existing) !== [] || array_diff($existing, $submitted) !== []) {
            throw ValidationException::withMessages(['order' => 'Submit each current sibling ID exactly once. Refresh and retry.']);
        }
        foreach ($submitted as $index => $id) {
            (clone $siblings)->where('id', $id)->update(['order' => $index + 1]);
        }
    }
}
