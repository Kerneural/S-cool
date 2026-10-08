<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Services\VideoEmbedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        if (! empty($validated['video_url']) && ! VideoEmbedService::isValid($validated['video_url'])) {
            throw ValidationException::withMessages([
                'video_url' => 'The video URL must be a valid HTTPS link from YouTube or Vimeo.',
            ]);
        }

        $maxOrder = (int) $section->lessons()->max('order');
        $validated['order'] = $maxOrder + 1;

        $section->lessons()->create($validated);

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
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        if (! empty($validated['video_url']) && ! VideoEmbedService::isValid($validated['video_url'])) {
            throw ValidationException::withMessages([
                'video_url' => 'The video URL must be a valid HTTPS link from YouTube or Vimeo.',
            ]);
        }

        $lesson->update($validated);

        return back()->with('status', 'Lesson updated successfully.');
    }

    public function destroy(Community $community, Course $course, CourseSection $section, Lesson $lesson): RedirectResponse
    {
        if ($course->community_id !== $community->id
            || $section->course_id !== $course->id
            || $lesson->course_section_id !== $section->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $lesson->delete();

        return redirect()->route('communities.courses.show', [$community, $course])
            ->with('status', 'Lesson deleted successfully.');
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

        $existingIds = $section->lessons()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $submittedIds = array_map('intval', $validated['order']);

        if (count($submittedIds) !== count(array_unique($submittedIds))) {
            return back()->withErrors(['order' => 'Duplicate sibling IDs in reorder payload.']);
        }

        if (count($submittedIds) !== count($existingIds)
            || array_diff($submittedIds, $existingIds) !== []
            || array_diff($existingIds, $submittedIds) !== []) {
            return back()->withErrors(['order' => 'Invalid or foreign sibling IDs in reorder payload.']);
        }

        DB::transaction(function () use ($submittedIds, $section) {
            foreach ($submittedIds as $index => $id) {
                $section->lessons()->where('id', $id)->update(['order' => $index + 1]);
            }
        });

        return back()->with('status', 'Lessons reordered successfully.');
    }
}
