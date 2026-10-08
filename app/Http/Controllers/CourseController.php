<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Services\ClassroomMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Request $request, Community $community, Course $course): View
    {
        // Ancestry and active community validation
        if (! $community->isActive() || $course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('view', $course);

        $user = $request->user();
        $isCreator = $community->isCreator($user);

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

        return view('classroom.courses.show', compact('community', 'course', 'isCreator'));
    }

    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('create', [Course::class, $community]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        ClassroomMutation::run($community, null, null, null, function ($community) use ($validated) {
            $community->courses()->create($validated + ['order' => (int) $community->courses()->max('order') + 1]);
        });

        return redirect()->route('communities.classroom.index', $community)
            ->with('status', 'Course created successfully.');
    }

    public function update(Request $request, Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        ClassroomMutation::run($community, $course, null, null, fn ($community, $course) => $course->update($validated));

        return back()->with('status', 'Course updated successfully.');
    }

    public function destroy(Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('delete', $course);

        abort(404); // Permanent content purge is outside this issue.
    }

    public function reorder(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('update', $community);

        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer'],
        ]);

        ClassroomMutation::run($community, null, null, null, fn ($community) => ClassroomMutation::reorder($community->courses(), $validated['order']));

        return back()->with('status', 'Courses reordered successfully.');
    }
}
