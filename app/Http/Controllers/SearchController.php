<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Lesson;
use App\Models\User;
use App\Services\Search\SearchNormalizationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            $escaped = SearchNormalizationService::escapeSqlWildcards($search);
            $tokens = SearchNormalizationService::tokenize($search);

            $applyFilter = function ($q, array $fields) use ($escaped, $tokens): void {
                $q->where(function ($sub) use ($escaped, $tokens, $fields): void {
                    foreach ($fields as $field) {
                        $sub->orWhere($field, 'like', "%{$escaped}%");
                    }
                    foreach (array_slice($tokens, 0, 4) as $token) {
                        if (mb_strlen($token) >= 3) {
                            $escToken = SearchNormalizationService::escapeSqlWildcards($token);
                            foreach ($fields as $field) {
                                $sub->orWhere($field, 'like', "%{$escToken}%");
                            }
                        }
                        if (mb_strlen($token) >= 4) {
                            $prefix2 = SearchNormalizationService::escapeSqlWildcards(mb_substr($token, 0, 2));
                            foreach ($fields as $field) {
                                $sub->orWhere($field, 'like', "%{$prefix2}%");
                            }
                        }
                    }
                });
            };

            // 1. Posts Query (Tenant-scoped, excluding soft-deleted)
            $postsQuery = $community->posts();
            $applyFilter($postsQuery, ['title', 'body']);

            // 2. Courses Query (Tenant-scoped, published & entitled for non-creators)
            $coursesQuery = $community->courses();
            if (! $isCreator) {
                $coursesQuery->published()->entitledTo($user);
            }
            $applyFilter($coursesQuery, ['title', 'description']);

            // 3. Lessons Query (Ancestry-checked within community, published only for non-creators, excluding locked paid courses)
            $lessonsQuery = Lesson::query()
                ->whereHas('courseSection.course', function ($q) use ($community, $isCreator, $user): void {
                    $q->where('community_id', $community->id);
                    if (! $isCreator) {
                        $q->where('status', 'PUBLISHED');
                        $q->entitledTo($user);
                    }
                });
            if (! $isCreator) {
                $lessonsQuery->published();
            }
            $applyFilter($lessonsQuery, ['title', 'content']);

            // 4. Members Query (Active community members & creator only)
            $membersQuery = User::query()
                ->where(function ($query) use ($community): void {
                    $query->where('id', $community->creator_id)
                        ->orWhereHas('memberships', function ($q) use ($community): void {
                            $q->where('community_id', $community->id)->effective();
                        });
                });
            $applyFilter($membersQuery, ['name', 'bio']);

            // 5. Events Query (Tenant-scoped, non-cancelled/scheduled only)
            $eventsQuery = $community->events()
                ->where('status', 'SCHEDULED');
            $applyFilter($eventsQuery, ['title', 'description']);

            // For each type, retrieve bounded candidates (max 50) and score them
            $scoreItems = function ($collection, callable $textExtractor) use ($search) {
                return $collection->map(function ($item) use ($search, $textExtractor) {
                    [$primary, $secondary] = $textExtractor($item);
                    $score1 = SearchNormalizationService::calculateScore((string) $primary, $search);
                    $score2 = $secondary ? (int) (SearchNormalizationService::calculateScore((string) $secondary, $search) * 0.7) : 0;
                    $item->search_score = max($score1, $score2);

                    return $item;
                })->filter(fn ($item): bool => ($item->search_score ?? 0) > 0)
                    ->sortByDesc('search_score')
                    ->values();
            };

            // Authorize/filter first; prioritize strong matches before the candidate cap.
            foreach ([$postsQuery, $coursesQuery, $lessonsQuery, $eventsQuery] as $candidateQuery) {
                SearchNormalizationService::prioritizeCandidates($candidateQuery, 'title', $search);
            }
            SearchNormalizationService::prioritizeCandidates($membersQuery, 'name', $search);

            // Candidates bounded to 50
            $postsCandidates = (clone $postsQuery)->with(['author:id,name', 'category'])->limit(50)->get();
            $scoredPosts = $scoreItems($postsCandidates, fn ($p) => [$p->title, $p->body]);

            $coursesCandidates = (clone $coursesQuery)->limit(50)->get();
            $scoredCourses = $scoreItems($coursesCandidates, fn ($c) => [$c->title, $c->description]);

            $lessonsCandidates = (clone $lessonsQuery)->with(['courseSection.course'])->limit(50)->get();
            $scoredLessons = $scoreItems($lessonsCandidates, fn ($l) => [$l->title, $l->content]);

            $membersCandidates = (clone $membersQuery)->select(['id', 'name', 'bio', 'avatar_path'])->limit(50)->get();
            $scoredMembers = $scoreItems($membersCandidates, fn ($m) => [$m->name, $m->bio]);

            $eventsCandidates = (clone $eventsQuery)->limit(50)->get();
            $scoredEvents = $scoreItems($eventsCandidates, fn ($e) => [$e->title, $e->description]);

            $counts = [
                'posts' => $scoredPosts->count(),
                'courses' => $scoredCourses->count(),
                'lessons' => $scoredLessons->count(),
                'members' => $scoredMembers->count(),
                'events' => $scoredEvents->count(),
            ];

            $activeCollection = match ($type) {
                'posts' => $scoredPosts,
                'courses' => $scoredCourses,
                'lessons' => $scoredLessons,
                'members' => $scoredMembers,
                'events' => $scoredEvents,
            };

            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 15;
            $itemsForCurrentPage = $activeCollection->slice(($page - 1) * $perPage, $perPage)->values();
            $results = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $activeCollection->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );
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
