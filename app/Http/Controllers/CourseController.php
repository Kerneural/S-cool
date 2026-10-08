<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $maxOrder = (int) $community->courses()->max('order');
        $validated['order'] = $maxOrder + 1;

        $community->courses()->create($validated);

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

        $course->update($validated);

        return back()->with('status', 'Course updated successfully.');
    }

    public function destroy(Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('delete', $course);

        $course->delete();

        return redirect()->route('communities.classroom.index', $community)
            ->with('status', 'Course deleted successfully.');
    }

    public function reorder(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('update', $community);

        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer'],
        ]);

        $existingIds = $community->courses()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $submittedIds = array_map('intval', $validated['order']);

        // Check for duplicate submitted IDs
        if (count($submittedIds) !== count(array_unique($submittedIds))) {
            return back()->withErrors(['order' => 'Duplicate sibling IDs in reorder payload.']);
        }

        // Must match exact set of existing course IDs
        if (count($submittedIds) !== count($existingIds)
            || array_diff($submittedIds, $existingIds) !== []
            || array_diff($existingIds, $submittedIds) !== []) {
            return back()->withErrors(['order' => 'Invalid or foreign sibling IDs in reorder payload.']);
        }

        DB::transaction(function () use ($submittedIds, $community) {
            foreach ($submittedIds as $index => $id) {
                $community->courses()->where('id', $id)->update(['order' => $index + 1]);
            }
        });

        return back()->with('status', 'Courses reordered successfully.');
    }
}
