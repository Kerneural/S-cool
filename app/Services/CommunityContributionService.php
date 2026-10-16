<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Community;
use App\Models\Course;
use App\Models\LessonProofOfWork;
use App\Models\PointContribution;
use App\Models\Post;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CommunityContributionService
{
    /**
     * Get verified skills summary for a member in a community.
     * Excludes summary, url, attachment_path and raw storage paths.
     *
     * @return array<string, mixed>
     */
    public function getVerifiedSkills(Community $community, User $user, ?User $viewer = null): array
    {
        $query = LessonProofOfWork::query()
            ->eligible()
            ->current()
            ->where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', LessonProofOfWork::STATUS_VERIFIED);
        $count = (clone $query)->count();
        $distinctCount = (clone $query)->whereNotNull('skill_label_snapshot')
            ->where('skill_label_snapshot', '!=', '')->distinct()->count('skill_label_snapshot');
        $records = $query->with(['course:id,community_id,title', 'lesson:id,title'])
            ->orderByDesc('reviewed_at')->orderByDesc('id')->limit(20)
            ->get(['id', 'course_id', 'lesson_id', 'revision', 'reviewed_at', 'skill_label_snapshot']);

        $viewer ??= $user;
        $permittedCourses = Course::query()->where('community_id', $community->id)->published()
            ->whereIn('id', $records->pluck('course_id'));
        if (! $community->isCreator($viewer)) {
            $permittedCourses->entitledTo($viewer);
        }
        $permittedIds = $permittedCourses->pluck('id')->all();

        $skills = $records->pluck('skill_label_snapshot')
            ->filter(fn ($label) => is_string($label) && trim($label) !== '')
            ->unique()
            ->values()
            ->all();

        return [
            'count' => $count,
            'distinct_count' => $distinctCount,
            'skills' => $skills,
            'evidence' => $records->map(fn ($record) => [
                'skill' => $record->skill_label_snapshot,
                'revision' => $record->revision,
                'reviewed_at' => $record->reviewed_at,
                'course' => in_array($record->course_id, $permittedIds, true) ? $record->course?->title : null,
                'lesson' => in_array($record->course_id, $permittedIds, true) ? $record->lesson?->title : null,
                'url' => in_array($record->course_id, $permittedIds, true)
                    ? route('communities.lessons.show', [$community, $record->course_id, $record->lesson_id]) : null,
            ])->all(),
        ];
    }

    /**
     * Get the count of creator-accepted helpful answers authored by this user in this community.
     * Inaccessible or deleted posts/comments stop contributing.
     */
    public function getHelpfulAnswersCount(Community $community, User $user): int
    {
        return Post::query()
            ->where('posts.community_id', $community->id)
            ->eligibleHelpfulAnswer()
            ->where('accepted_helpful_comment.user_id', $user->id)
            ->count();
    }

    /** Bounded, safe activity history; no comment bodies or evidence payloads. */
    public function getActivityHistory(Community $community, User $user, User $viewer): array
    {
        $start = CarbonImmutable::now('UTC')->startOfDay()->subDays(89);
        $end = CarbonImmutable::now('UTC')->endOfDay();
        $posts = Post::query()->where('posts.community_id', $community->id)->where('posts.user_id', $user->id)
            ->whereBetween('posts.created_at', [$start, $end])
            ->visibleActivityFor($user)->visibleActivityFor($viewer)->latest('posts.created_at')->orderByDesc('posts.id')->limit(10)
            ->get(['posts.id', 'posts.title', 'posts.created_at'])
            ->map(fn ($post) => ['kind' => 'Post', 'title' => $post->title, 'at' => $post->created_at,
                'url' => route('communities.posts.show', [$community, $post])]);
        $comments = Comment::query()->where('comments.user_id', $user->id)
            ->whereBetween('comments.created_at', [$start, $end])
            ->whereHas('post', fn ($q) => $q->where('posts.community_id', $community->id)
                ->visibleActivityFor($user)->visibleActivityFor($viewer))
            ->with('post:id,title')->latest('comments.created_at')->orderByDesc('comments.id')->limit(10)
            ->get(['comments.id', 'comments.post_id', 'comments.created_at'])
            ->map(fn ($comment) => ['kind' => 'Comment', 'title' => $comment->post->title, 'at' => $comment->created_at,
                'url' => route('communities.posts.show', [$community, $comment->post_id])]);

        return $posts->concat($comments)->sortByDesc('at')->take(10)->values()->all();
    }

    /** Uses the existing lifetime engagement ranking; no alternate scoring formula. */
    public function miniRanking(Community $community): array
    {
        $points = PointContribution::query()->eligible($community, 'lifetime')
            ->select('beneficiary_id')->selectRaw('SUM(points) as total_points')->groupBy('beneficiary_id');

        return PointContribution::eligibleUsersQuery($community)
            ->leftJoinSub($points, 'mini_points', fn ($join) => $join->on('users.id', '=', 'mini_points.beneficiary_id'))
            ->select('users.id', 'users.name', DB::raw('COALESCE(mini_points.total_points, 0) as points'))
            ->orderByDesc('points')->orderBy('users.id')->limit(3)->get()
            ->map(fn ($entry, $index) => ['id' => $entry->id, 'name' => $entry->name,
                'rank' => $index + 1, 'points' => (int) $entry->points])->all();
    }

    /**
     * Compute a 90-day UTC activity heatmap for the user scoped to this community.
     * Uses bounded aggregate queries; no per-cell database queries.
     *
     * @return array{
     *     days: list<array{date: string, post_count: int, comment_count: int, total_count: int, level: int}>,
     *     total_contributions: int,
     *     total_posts: int,
     *     total_comments: int,
     *     start_date: string,
     *     end_date: string,
     *     accessible_summary: string
     * }
     */
    public function getActivityHeatmap(Community $community, User $user, ?User $viewer = null): array
    {
        $viewer ??= $user;
        $today = CarbonImmutable::now('UTC')->startOfDay();
        $startDate = $today->subDays(89);
        $endDate = $today->endOfDay();

        // Single aggregate query for posts
        $postCounts = Post::query()
            ->where('posts.community_id', $community->id)
            ->where('posts.user_id', $user->id)
            ->visibleActivityFor($user)->visibleActivityFor($viewer)
            ->whereBetween('posts.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->all();

        // Single aggregate query for comments within this community's visible posts
        $commentCounts = Comment::query()
            ->where('comments.user_id', $user->id)
            ->whereHas('post', function ($query) use ($community, $user, $viewer): void {
                $query->where('posts.community_id', $community->id)->visibleActivityFor($user)->visibleActivityFor($viewer);
            })
            ->whereBetween('comments.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->all();

        $days = [];
        $totalContributions = 0;
        $totalPosts = 0;
        $totalComments = 0;

        for ($i = 0; $i < 90; $i++) {
            $day = $startDate->addDays($i);
            $dateStr = $day->format('Y-m-d');

            $pCount = (int) ($postCounts[$dateStr] ?? 0);
            $cCount = (int) ($commentCounts[$dateStr] ?? 0);
            $dayTotal = $pCount + $cCount;

            $totalPosts += $pCount;
            $totalComments += $cCount;
            $totalContributions += $dayTotal;

            $level = 0;
            if ($dayTotal >= 5) {
                $level = 4;
            } elseif ($dayTotal >= 3) {
                $level = 3;
            } elseif ($dayTotal >= 2) {
                $level = 2;
            } elseif ($dayTotal >= 1) {
                $level = 1;
            }

            $days[] = [
                'date' => $dateStr,
                'post_count' => $pCount,
                'comment_count' => $cCount,
                'total_count' => $dayTotal,
                'level' => $level,
            ];
        }

        $accessibleSummary = $totalContributions === 0
            ? '0 contributions in the last 90 UTC days.'
            : sprintf(
                '%d contributions (%d posts, %d comments) in the last 90 UTC days from %s to %s.',
                $totalContributions,
                $totalPosts,
                $totalComments,
                $startDate->format('M d, Y'),
                $today->format('M d, Y')
            );

        return [
            'days' => $days,
            'total_contributions' => $totalContributions,
            'total_posts' => $totalPosts,
            'total_comments' => $totalComments,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $today->format('Y-m-d'),
            'accessible_summary' => $accessibleSummary,
        ];
    }
}
