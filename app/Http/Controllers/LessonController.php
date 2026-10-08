<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Services\ClassroomMutation;
use App\Services\VideoEmbedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Request $request, Community $community, Course $course, Lesson $lesson): View
    {
        // Strict ancestry and community check
        if (! $community->isActive()
            || $course->community_id !== $community->id
            || $lesson->section?->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('view', $lesson);

        $user = $request->user();
        $isCreator = $community->isCreator($user);

        // Non-creators cannot view if either course or lesson is DRAFT
        if (! $isCreator && (! $course->isPublished() || ! $lesson->isPublished())) {
            abort(404);
        }

        // Load all course sections and lessons for navigation sidebar
        $course->load([
            'sections' => function ($query) use ($isCreator) {
                $query->ordered();
                if (! $isCreator) {
                    $query->whereHas('lessons', fn ($q) => $q->published());
                }
            },
            'sections.lessons' => function ($query) use ($isCreator) {
                $query->ordered();
                if (! $isCreator) {
                    $query->published();
                }
            },
        ]);

        // Find previous and next lesson for navigation
        $allLessons = $course->sections->flatMap->lessons;
        $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $allLessons->get($currentIndex - 1) : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1 ? $allLessons->get($currentIndex + 1) : null;

        return view('classroom.lessons.show', compact('community', 'course', 'lesson', 'isCreator', 'prevLesson', 'nextLesson'));
    }

    public function store(Request $request, Community $community, Course $course, CourseSection $section): RedirectResponse
    {
        if ($course->community_id !== $community->id || $section->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required_without:video_url', 'nullable', 'string', 'max:50000'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        if (($validated['video_url'] ?? null) !== null && ! VideoEmbedService::isValid($validated['video_url'])) {
            throw ValidationException::withMessages([
                'video_url' => 'The video URL must be a valid HTTPS link from YouTube or Vimeo.',
            ]);
        }

        ClassroomMutation::run($community, $course, $section, null, function ($community, $course, $section) use ($validated) {
            $section->lessons()->create($validated + ['order' => (int) $section->lessons()->max('order') + 1]);
        });

        return back()->with('status', 'Lesson created successfully.');
    }

    public function update(Request $request, Community $community, Course $course, CourseSection $section, Lesson $lesson): RedirectResponse
    {
        if ($course->community_id !== $community->id
            || $section->course_id !== $course->id
            || $lesson->course_section_id !== $section->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required_without:video_url', 'nullable', 'string', 'max:50000'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        if (($validated['video_url'] ?? null) !== null && ! VideoEmbedService::isValid($validated['video_url'])) {
            throw ValidationException::withMessages([
                'video_url' => 'The video URL must be a valid HTTPS link from YouTube or Vimeo.',
            ]);
        }

        ClassroomMutation::run($community, $course, $section, $lesson, fn ($community, $course, $section, $lesson) => $lesson->update($validated));

        return back()->with('status', 'Lesson updated successfully.');
    }

    public function destroy(Community $community, Course $course, CourseSection $section, Lesson $lesson): RedirectResponse
    {
        if ($course->community_id !== $community->id
            || $section->course_id !== $course->id
            || $lesson->course_section_id !== $section->id) {
            abort(404);
        }

        Gate::authorize('delete', $lesson);

        abort(404); // Use unpublish; content/progress must be retained.
    }

    public function reorder(Request $request, Community $community, Course $course, CourseSection $section): RedirectResponse
    {
        if ($course->community_id !== $community->id || $section->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer'],
        ]);

        ClassroomMutation::run($community, $course, $section, null, fn ($community, $course, $section) => ClassroomMutation::reorder($section->lessons(), $validated['order']));

        return back()->with('status', 'Lessons reordered successfully.');
    }
}
