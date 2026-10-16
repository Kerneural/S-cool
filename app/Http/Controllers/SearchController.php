<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search results across posts, courses, lessons, and members in the community.
     */
    public function index(Request $request, Community $community): View
    {
        if (! $community->isActive()) {
            abort(404);
        }

        Gate::authorize('view', $community);

        $user = $request->user();
        $isCreator = $community->isCreator($user);
        $isActiveMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isActiveMember) {
            abort(404);
        }

        $type = $request->query('type', 'posts');
        if (! is_string($type) || ! in_array($type, ['posts', 'courses', 'lessons', 'members', 'events'], true)) {
            throw ValidationException::withMessages([
                'type' => 'The selected search type is invalid.',
            ]);
        }

        $rawQ = $request->query('q');
        $search = null;

        if ($request->has('q')) {
            if (! is_string($rawQ)) {
                throw ValidationException::withMessages([
                    'q' => 'The search query must be a valid text string.',
                ]);
            }

            $trimmed = trim($rawQ);
            $length = mb_strlen($trimmed);

            if ($length < 2 || $length > 100) {
                throw ValidationException::withMessages([
                    'q' => 'The search query must be between 2 and 100 characters.',
                ]);
            }

            $search = $trimmed;
        }

        $counts = [
            'posts' => 0,
            'courses' => 0,
            'lessons' => 0,
            'members' => 0,
            'events' => 0,
        ];
        $results = null;

        if ($search !== null && $search !== '') {
            $escaped = addcslashes($search, '%_\\');

            // 1. Posts Query (Tenant-scoped, excluding soft-deleted)
            $postsQuery = $community->posts()
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('body', 'like', "%{$escaped}%");
                });

            // 2. Courses Query (Tenant-scoped, published & entitled for non-creators)
            $coursesQuery = $community->courses()
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%");
                });

            if (! $isCreator) {
                $coursesQuery->published()->entitledTo($user);
            }

            // 3. Lessons Query (Ancestry-checked within community, published only for non-creators, excluding locked paid courses)
            $lessonsQuery = Lesson::query()
                ->whereHas('courseSection.course', function ($q) use ($community, $isCreator, $user): void {
                    $q->where('community_id', $community->id);
                    if (! $isCreator) {
                        $q->where('status', 'PUBLISHED');
                        $q->entitledTo($user);
                    }
                })
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('content', 'like', "%{$escaped}%");
                });

            if (! $isCreator) {
                $lessonsQuery->published();
            }

            // 4. Members Query (Active community members & creator only)
            $membersQuery = User::query()
                ->where(function ($query) use ($community): void {
                    $query->where('id', $community->creator_id)
                        ->orWhereHas('memberships', function ($q) use ($community): void {
                            $q->where('community_id', $community->id)->effective();
                        });
                })
                ->where(function ($q) use ($escaped): void {
                    $q->where('name', 'like', "%{$escaped}%")
                        ->orWhere('bio', 'like', "%{$escaped}%");
                });

            // 5. Events Query (Tenant-scoped, non-cancelled/scheduled only)
            $eventsQuery = $community->events()
                ->where('status', 'SCHEDULED')
                ->where(function ($q) use ($escaped): void {
                    $q->where('title', 'like', "%{$escaped}%")
                        ->orWhere('description', 'like', "%{$escaped}%");
                });

            // Counts using identical permission and ancestry filters
            $counts = [
                'posts' => (clone $postsQuery)->count(),
                'courses' => (clone $coursesQuery)->count(),
                'lessons' => (clone $lessonsQuery)->count(),
                'members' => (clone $membersQuery)->count(),
                'events' => (clone $eventsQuery)->count(),
            ];

            // Only paginate the currently selected result type
            $results = match ($type) {
                'posts' => $postsQuery->with(['author:id,name', 'category'])->orderByDesc('id')->paginate(15)->withQueryString(),
                'courses' => $coursesQuery->orderBy('order')->orderByDesc('id')->paginate(15)->withQueryString(),
                'lessons' => $lessonsQuery->with(['courseSection.course'])->orderBy('id')->paginate(15)->withQueryString(),
                'members' => $membersQuery->select(['id', 'name', 'bio', 'avatar_path'])->orderBy('name')->paginate(15)->withQueryString(),
                'events' => $eventsQuery->orderBy('starts_at')->paginate(15)->withQueryString(),
            };
        }

        return view('communities.search', [
            'community' => $community,
            'searchQuery' => $search,
            'type' => $type,
            'counts' => $counts,
            'results' => $results,
            'isCreator' => $isCreator,
        ]);
    }
}
