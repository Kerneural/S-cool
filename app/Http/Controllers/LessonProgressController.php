<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonProgressMutation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LessonProgressController extends Controller
{
    /**
     * Update or create the authenticated member's progress for a published lesson.
     */
    public function update(Request $request, Community $community, Course $course, Lesson $lesson): JsonResponse|RedirectResponse
    {
        // Strict ancestry and active community validation
        if (! $community->isActive()
            || $course->community_id !== $community->id
            || $lesson->section?->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('updateProgress', $lesson);

        $validated = $request->validate([
            'completed' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $isCompleted = (bool) $validated['completed'];
        $result = LessonProgressMutation::run($user, $community, $course, $lesson, $isCompleted);
        $progress = $result['progress'];
        $summary = $result['summary'];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'lesson_id' => $lesson->id,
                'completed' => (bool) $progress->completed,
                'completed_at' => $progress->completed_at?->toIso8601String(),
                'course_progress_percentage' => $summary['percentage'],
                'course_completed_lessons_count' => $summary['completed'],
                'course_published_lessons_count' => $summary['total'],
            ]);
        }

        return redirect()->route('communities.lessons.show', [$community, $course, $lesson])
            ->with('status', $isCompleted ? __('Lesson marked as completed.') : __('Lesson marked as incomplete.'));
    }
}
