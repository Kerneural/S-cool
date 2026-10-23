<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CourseEntitlement;
use App\Services\LearningFocusService;
use App\Services\Search\SearchNormalizationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('view', $community);

        $user = $request->user();
        $isCreator = $community->isCreator($user);
        $isActiveMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        $coursesQuery = $community->courses()->ordered();

        if (! $isCreator) {
            $coursesQuery->published();
        }

        $rawQ = $request->query('q');
        $search = is_string($rawQ) ? trim(mb_substr($rawQ, 0, 100)) : '';

        $courseOrder = $isCreator ? (clone $coursesQuery)->pluck('id')->all() : [];
        if ($isActiveMember) {
            $coursesQuery->withCount([
                'lessons as progress_total' => fn ($query) => $query->published(),
                'lessons as progress_completed' => fn ($query) => $query->published()->whereHas('progresses', fn ($q) => $q->where('user_id', $user->id)->where('completed', true)),
            ]);
        }
        $coursesQuery->withCount(['lessons as visible_lessons_count' => function ($query) use ($isCreator) {
            if (! $isCreator) {
                $query->published();
            }
        }]);

        if ($search !== '') {
            $escaped = SearchNormalizationService::escapeSqlWildcards($search);
            $tokens = SearchNormalizationService::tokenize($search);

            $coursesQuery->where(function ($q) use ($escaped, $tokens): void {
                $q->where('title', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%");

                foreach (array_slice($tokens, 0, 4) as $token) {
                    if (mb_strlen($token) >= 3) {
                        $escToken = SearchNormalizationService::escapeSqlWildcards($token);
                        $q->orWhere('title', 'like', "%{$escToken}%");
                    }
                    if (mb_strlen($token) >= 4) {
                        $prefix2 = SearchNormalizationService::escapeSqlWildcards(mb_substr($token, 0, 2));
                        $q->orWhere('title', 'like', "%{$prefix2}%");
                    }
                }
            });

            // Bounded candidate retrieval (max 50 to prevent memory exhaustion)
            $candidates = $coursesQuery->take(50)->get();

            $scored = $candidates->map(function ($course) use ($search) {
                $titleScore = SearchNormalizationService::calculateScore($course->title, $search);
                $descScore = $course->description ? (int) (SearchNormalizationService::calculateScore($course->description, $search) * 0.8) : 0;
                $course->search_score = max($titleScore, $descScore);

                return $course;
            })->filter(fn ($c): bool => ($c->search_score ?? 0) > 0)
                ->sort(function ($a, $b) {
                    if ($a->search_score === $b->search_score) {
                        return $a->order <=> $b->order;
                    }

                    return $b->search_score <=> $a->search_score;
                })
                ->values();

            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 12;
            $itemsForCurrentPage = $scored->slice(($page - 1) * $perPage, $perPage)->values();
            $courses = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $scored->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
            );
        } else {
            $courses = $coursesQuery->paginate(12)->withQueryString();
        }

        // Resolve catalog access once for this page; rendering cards must not
        // re-query membership and entitlement for every published course.
        $entitledCourseIds = $isActiveMember && ! $isCreator
            ? CourseEntitlement::query()->where('user_id', $user->id)
                ->whereIn('course_id', $courses->getCollection()->modelKeys())
                ->pluck('course_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $learningFocus = LearningFocusService::resolve($community, $user);

        return view('classroom.index', [
            'community' => $community,
            'courses' => $courses,
            'isCreator' => $isCreator,
            'courseOrder' => $courseOrder,
            'isActiveMember' => $isActiveMember,
            'learningFocus' => $learningFocus,
            'entitledCourseIds' => $entitledCourseIds,
            'searchQuery' => $search,
        ]);
    }
}
