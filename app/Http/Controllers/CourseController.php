<?php

namespace App\Http\Controllers;

use App\Jobs\SendCoursePublishedNotification;
use App\Models\Community;
use App\Models\Course;
use App\Services\ClassroomMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
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
        $isActiveMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        $isEntitled = $isCreator || $course->hasMemberAccess($user);

        if ($isEntitled) {
            $course->load([
                'sections' => function ($query) use ($isCreator) {
                    $query->ordered();
                    if (! $isCreator) {
                        $query->whereHas('lessons', fn ($q) => $q->published());
                    }
                },
                'sections.lessons' => function ($query) use ($isCreator, $user) {
                    $query->ordered();
                    if (! $isCreator) {
                        $query->published();
                    }
                    $query->with(['progresses' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }]);
                },
            ]);

        } else {
            $course->setRelation('sections', collect());
        }

        $progressSummary = $isActiveMember && $isEntitled ? $course->progressSummaryFor($user) : null;

        return view('classroom.courses.show', compact('community', 'course', 'isCreator', 'isActiveMember', 'progressSummary', 'isEntitled'));
    }

    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('create', [Course::class, $community]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'access_mode' => ['nullable', 'in:INCLUDED,ONE_TIME'],
            'price' => ['nullable', 'required_if:access_mode,ONE_TIME', 'integer', 'min:1', 'max:4294967295'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        $mode = $validated['access_mode'] ?? Course::ACCESS_MODE_INCLUDED;
        $price = $mode === Course::ACCESS_MODE_ONE_TIME ? (int) ($validated['price'] ?? 0) : null;
        if ($mode === Course::ACCESS_MODE_ONE_TIME && ($price === null || $price <= 0)) {
            throw ValidationException::withMessages(['price' => 'Set a positive whole-VND price for individual course purchase.']);
        }

        ClassroomMutation::run($community, null, null, null, function ($community) use ($validated, $mode, $price) {
            $publishedRevision = $validated['status'] === 'PUBLISHED' ? 1 : 0;
            $course = $community->courses()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'access_mode' => $mode,
                'price' => $price,
                'status' => $validated['status'],
                'order' => (int) $community->courses()->max('order') + 1,
                'published_revision' => $publishedRevision,
            ]);

            if ($validated['status'] === 'PUBLISHED') {
                SendCoursePublishedNotification::dispatch($course->id, $publishedRevision);
            }
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
            'access_mode' => ['nullable', 'in:INCLUDED,ONE_TIME'],
            'price' => ['nullable', 'required_if:access_mode,ONE_TIME', 'integer', 'min:1', 'max:4294967295'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        $mode = $validated['access_mode'] ?? $course->access_mode ?? Course::ACCESS_MODE_INCLUDED;
        $price = $mode === Course::ACCESS_MODE_ONE_TIME ? (int) ($validated['price'] ?? $course->price ?? 0) : null;
        if ($mode === Course::ACCESS_MODE_ONE_TIME && ($price === null || $price <= 0)) {
            throw ValidationException::withMessages(['price' => 'Set a positive whole-VND price for individual course purchase.']);
        }

        ClassroomMutation::run($community, $course, null, null, function ($community, $course) use ($validated, $mode, $price) {
            $newStatus = $validated['status'];
            $currentLockedStatus = $course->status;
            $publishedRevision = (int) $course->published_revision;
            $transitionedToPublished = $currentLockedStatus !== 'PUBLISHED' && $newStatus === 'PUBLISHED';

            if ($transitionedToPublished) {
                $publishedRevision++;
            }

            $course->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'access_mode' => $mode,
                'price' => $price,
                'status' => $newStatus,
                'published_revision' => $publishedRevision,
            ]);

            if ($transitionedToPublished) {
                SendCoursePublishedNotification::dispatch($course->id, $publishedRevision);
            }
        });

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
