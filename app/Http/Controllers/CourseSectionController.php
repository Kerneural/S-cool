<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CourseSectionController extends Controller
{
    public function store(Request $request, Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = (int) $course->sections()->max('order');
        $validated['order'] = $maxOrder + 1;

        $course->sections()->create($validated);

        return back()->with('status', 'Section created successfully.');
    }

    public function update(Request $request, Community $community, Course $course, CourseSection $section): RedirectResponse
    {
        if ($course->community_id !== $community->id || $section->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $section->update($validated);

        return back()->with('status', 'Section updated successfully.');
    }

    public function destroy(Community $community, Course $course, CourseSection $section): RedirectResponse
    {
        if ($course->community_id !== $community->id || $section->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $section->delete();

        return back()->with('status', 'Section deleted successfully.');
    }

    public function reorder(Request $request, Community $community, Course $course): RedirectResponse
    {
        if ($course->community_id !== $community->id) {
            abort(404);
        }

        Gate::authorize('update', $course);

        $validated = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer'],
        ]);

        $existingIds = $course->sections()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $submittedIds = array_map('intval', $validated['order']);

        if (count($submittedIds) !== count(array_unique($submittedIds))) {
            return back()->withErrors(['order' => 'Duplicate sibling IDs in reorder payload.']);
        }

        if (count($submittedIds) !== count($existingIds)
            || array_diff($submittedIds, $existingIds) !== []
            || array_diff($existingIds, $submittedIds) !== []) {
            return back()->withErrors(['order' => 'Invalid or foreign sibling IDs in reorder payload.']);
        }

        DB::transaction(function () use ($submittedIds, $course) {
            foreach ($submittedIds as $index => $id) {
                $course->sections()->where('id', $id)->update(['order' => $index + 1]);
            }
        });

        return back()->with('status', 'Sections reordered successfully.');
    }
}
