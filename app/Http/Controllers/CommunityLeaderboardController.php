<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\PointContribution;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommunityLeaderboardController extends Controller
{
    /**
     * Display the community points leaderboard and personal member progress.
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

        $period = $request->query('period', 'lifetime');
        if (! in_array($period, ['lifetime', 'month'], true)) {
            throw ValidationException::withMessages([
                'period' => 'Invalid leaderboard period selected.',
            ]);
        }

        $leaderboard = PointContribution::getLeaderboard($community, $period, 20);
        $leaderboard->appends(['period' => $period]);

        $lifetimePoints = PointContribution::getUserPoints($community, $user, 'lifetime');
        $monthPoints = PointContribution::getUserPoints($community, $user, 'month');
        $userProgress = PointContribution::nextLevelProgress($lifetimePoints);
        $userRank = PointContribution::getUserRank($community, $user, $period);

        return view('communities.leaderboard', [
            'community' => $community,
            'leaderboard' => $leaderboard,
            'period' => $period,
            'lifetimePoints' => $lifetimePoints,
            'monthPoints' => $monthPoints,
            'userProgress' => $userProgress,
            'userRank' => $userRank,
        ]);
    }
}
