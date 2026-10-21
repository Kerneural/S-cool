<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\LessonProofOfWork;
use App\Models\PointContribution;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommunityLeaderboardController extends Controller
{
    /**
     * Display the community leaderboard with distinct metric perspectives (AC-03):
     * 1. Engagement points (F-10)
     * 2. Verified practical skills (EUR-50/51)
     * 3. Creator-accepted helpful answers (EUR-51)
     */
    public function index(Request $request, Community $community): View
    {
        if (! $community->isActive()) {
            abort(404);
        }

        $user = $request->user();
        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isMember) {
            abort(404);
        }

        $metric = $request->query('metric', 'engagement');
        if (! in_array($metric, ['engagement', 'verified_skills', 'helpful_answers'], true)) {
            throw ValidationException::withMessages([
                'metric' => 'Invalid leaderboard metric selected.',
            ]);
        }

        $period = $request->query('period', 'lifetime');
        if (! in_array($period, ['lifetime', 'month'], true)) {
            throw ValidationException::withMessages([
                'period' => 'Invalid leaderboard period selected.',
            ]);
        }

        $lifetimePoints = PointContribution::getUserPoints($community, $user, 'lifetime');
        $monthPoints = PointContribution::getUserPoints($community, $user, 'month');
        $userProgress = PointContribution::nextLevelProgress($lifetimePoints);

        if ($metric === 'engagement') {
            $leaderboard = PointContribution::getLeaderboard($community, $period, 20);
            $leaderboard->appends(['metric' => $metric, 'period' => $period]);
            $userRank = PointContribution::getUserRank($community, $user, $period);
            $userMetricScore = $period === 'month' ? $monthPoints : $lifetimePoints;
        } elseif ($metric === 'verified_skills') {
            [$leaderboard, $userRank, $userMetricScore] = $this->getVerifiedSkillsLeaderboard($community, $user);
            $leaderboard->appends(['metric' => $metric]);
        } else {
            [$leaderboard, $userRank, $userMetricScore] = $this->getHelpfulAnswersLeaderboard($community, $user);
            $leaderboard->appends(['metric' => $metric]);
        }

        return view('communities.leaderboard', [
            'community' => $community,
            'leaderboard' => $leaderboard,
            'metric' => $metric,
            'period' => $period,
            'lifetimePoints' => $lifetimePoints,
            'monthPoints' => $monthPoints,
            'userProgress' => $userProgress,
            'userRank' => $userRank,
            'userMetricScore' => $userMetricScore,
        ]);
    }

    /**
     * @return array{0: LengthAwarePaginator, 1: ?int, 2: int}
     */
    private function getVerifiedSkillsLeaderboard(Community $community, $user): array
    {
        $verifiedSubquery = LessonProofOfWork::query()
            ->eligible()
            ->current()
            ->where('lesson_proof_of_works.community_id', $community->id)
            ->where('lesson_proof_of_works.status', LessonProofOfWork::STATUS_VERIFIED)
            ->select('lesson_proof_of_works.user_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('lesson_proof_of_works.user_id');

        $baseQuery = PointContribution::eligibleUsersQuery($community)
            ->leftJoinSub($verifiedSubquery, 'verified_counts', function ($join): void {
                $join->on('users.id', '=', 'verified_counts.user_id');
            })
            ->select('users.id', 'users.name', 'users.avatar_path', DB::raw('COALESCE(verified_counts.total_count, 0) as score'))
            ->orderByDesc('score')
            ->orderBy('users.id');

        $userScore = (int) (clone $baseQuery)->where('users.id', $user->id)->value('score');

        $aheadCount = (clone $baseQuery)
            ->where(function (Builder $q) use ($userScore, $user): void {
                $q->where(DB::raw('COALESCE(verified_counts.total_count, 0)'), '>', $userScore)
                    ->orWhere(function (Builder $sub) use ($userScore, $user): void {
                        $sub->where(DB::raw('COALESCE(verified_counts.total_count, 0)'), '=', $userScore)
                            ->where('users.id', '<', $user->id);
                    });
            })
            ->count();

        $userRank = $aheadCount + 1;

        $paginator = $baseQuery->paginate(20);
        $page = $paginator->currentPage();
        $perPage = $paginator->perPage();

        $paginator->getCollection()->transform(function ($item, $index) use ($page, $perPage) {
            return (object) ['rank' => ($page - 1) * $perPage + $index + 1,
                'user' => $item, 'points' => (int) $item->score];
        });

        return [$paginator, $userRank, $userScore];
    }

    /**
     * @return array{0: LengthAwarePaginator, 1: ?int, 2: int}
     */
    private function getHelpfulAnswersLeaderboard(Community $community, $user): array
    {
        $helpfulSubquery = Post::query()
            ->where('posts.community_id', $community->id)
            ->eligibleHelpfulAnswer()
            ->select('accepted_helpful_comment.user_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('accepted_helpful_comment.user_id');

        $baseQuery = PointContribution::eligibleUsersQuery($community)
            ->leftJoinSub($helpfulSubquery, 'helpful_counts', function ($join): void {
                $join->on('users.id', '=', 'helpful_counts.user_id');
            })
            ->select('users.id', 'users.name', 'users.avatar_path', DB::raw('COALESCE(helpful_counts.total_count, 0) as score'))
            ->orderByDesc('score')
            ->orderBy('users.id');

        $userScore = (int) (clone $baseQuery)->where('users.id', $user->id)->value('score');

        $aheadCount = (clone $baseQuery)
            ->where(function (Builder $q) use ($userScore, $user): void {
                $q->where(DB::raw('COALESCE(helpful_counts.total_count, 0)'), '>', $userScore)
                    ->orWhere(function (Builder $sub) use ($userScore, $user): void {
                        $sub->where(DB::raw('COALESCE(helpful_counts.total_count, 0)'), '=', $userScore)
                            ->where('users.id', '<', $user->id);
                    });
            })
            ->count();

        $userRank = $aheadCount + 1;

        $paginator = $baseQuery->paginate(20);
        $page = $paginator->currentPage();
        $perPage = $paginator->perPage();

        $paginator->getCollection()->transform(function ($item, $index) use ($page, $perPage) {
            return (object) ['rank' => ($page - 1) * $perPage + $index + 1,
                'user' => $item, 'points' => (int) $item->score];
        });

        return [$paginator, $userRank, $userScore];
    }
}
