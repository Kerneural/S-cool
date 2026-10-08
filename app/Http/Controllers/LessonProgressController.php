<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Database\UniqueConstraintViolationException;
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
        $completedAt = $isCompleted ? now() : null;

        try {
            $progress = LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['completed' => $isCompleted, 'completed_at' => $completedAt]
            );
        } catch (UniqueConstraintViolationException) {
            $progress = LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['completed' => $isCompleted, 'completed_at' => $completedAt]
            );
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'lesson_id' => $lesson->id,
                'completed' => (bool) $progress->completed,
                'completed_at' => $progress->completed_at?->toIso8601String(),
                'course_progress_percentage' => $course->progressPercentageFor($user),
                'course_completed_lessons_count' => $course->completedLessonsCountFor($user),
                'course_published_lessons_count' => $course->publishedLessonsCount(),
            ]);
        }

        return back()->with('status', $isCompleted ? __('Lesson marked as completed.') : __('Lesson marked as incomplete.'));
    }
}
