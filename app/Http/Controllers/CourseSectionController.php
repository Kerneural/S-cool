<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Services\ClassroomMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        ClassroomMutation::run($community, $course, null, null, function ($community, $course) use ($validated) {
            $course->sections()->create($validated + ['order' => (int) $course->sections()->max('order') + 1]);
        });

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

        ClassroomMutation::run($community, $course, $section, null, fn ($community, $course, $section) => $section->update($validated));

        return back()->with('status', 'Section updated successfully.');
    }

    public function destroy(Community $community, Course $course, CourseSection $section): RedirectResponse
    {
        if ($course->community_id !== $community->id || $section->course_id !== $course->id) {
            abort(404);
        }

        Gate::authorize('delete', $section);

        abort(404); // Permanent content purge is outside this issue.

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

        ClassroomMutation::run($community, $course, null, null, fn ($community, $course) => ClassroomMutation::reorder($course->sections(), $validated['order']));

        return back()->with('status', 'Sections reordered successfully.');
    }
}
