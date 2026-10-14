<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PointContribution extends Model
{
    use HasFactory;

    /**
     * Thresholds defining levels 1 to 5.
     * Level 1: 0, Level 2: 5, Level 3: 15, Level 4: 30, Level 5: 60.
     */
    public const THRESHOLDS = [
        1 => 0,
        2 => 5,
        3 => 15,
        4 => 30,
        5 => 60,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'source_type',
        'source_id',
        'beneficiary_id',
        'actor_id',
        'points',
        'is_active',
        'contributed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points' => 'integer',
            'contributed_at' => 'datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Calculate user level (1 to 5) from lifetime points.
     */
    public static function levelForPoints(int $points): int
    {
        if ($points >= self::THRESHOLDS[5]) {
            return 5;
        }
        if ($points >= self::THRESHOLDS[4]) {
            return 4;
        }
        if ($points >= self::THRESHOLDS[3]) {
            return 3;
        }
        if ($points >= self::THRESHOLDS[2]) {
            return 2;
        }

        return 1;
    }

    /**
     * Calculate progress toward the next level based on lifetime points.
     *
     * @return array{level: int, points: int, next_threshold: ?int, points_needed: int, progress_pct: int}
     */
    public static function nextLevelProgress(int $points): array
    {
        $level = self::levelForPoints($points);

        if ($level === 5) {
            return [
                'level' => 5,
                'points' => $points,
                'next_threshold' => null,
                'points_needed' => 0,
                'progress_pct' => 100,
            ];
        }

        $currentBase = self::THRESHOLDS[$level];
        $nextThreshold = self::THRESHOLDS[$level + 1];
        $range = $nextThreshold - $currentBase;
        $earnedInRange = max(0, $points - $currentBase);
        $pointsNeeded = max(0, $nextThreshold - $points);
        $progressPct = $range > 0 ? (int) round(($earnedInRange / $range) * 100) : 0;

        return [
            'level' => $level,
            'points' => $points,
            'next_threshold' => $nextThreshold,
            'points_needed' => $pointsNeeded,
            'progress_pct' => min(100, max(0, $progressPct)),
        ];
    }

    /**
     * Query scope to filter only eligible point contributions in a community.
     * Verifies:
     * 1. Contribution is active and non-self.
     * 2. Source post is not soft-deleted and source like is active.
     * 3. Beneficiary is creator OR active member of the community.
     * 4. Actor is creator OR active member of the community.
     * 5. If period is 'month', filtered by current UTC month boundaries.
     *
     * @param  Builder<PointContribution>  $query
     * @return Builder<PointContribution>
     */
    public function scopeEligible(Builder $query, Community $community, string $period = 'lifetime'): Builder
    {
        $query->where('point_contributions.community_id', $community->id)
            ->where('point_contributions.is_active', true)
            ->whereColumn('point_contributions.actor_id', '!=', 'point_contributions.beneficiary_id')
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('post_likes')
                    ->join('posts', 'posts.id', '=', 'post_likes.post_id')
                    ->whereColumn('post_likes.id', 'point_contributions.source_id')
                    ->where('point_contributions.source_type', 'post_like')
                    ->where('post_likes.is_active', true)
                    ->whereNull('posts.deleted_at');
            })
            ->where(function (Builder $sub) use ($community) {
                $sub->where('point_contributions.beneficiary_id', $community->creator_id)
                    ->orWhereExists(function ($memberSub) use ($community) {
                        $memberSub->selectRaw('1')
                            ->from('community_memberships')
                            ->whereColumn('community_memberships.user_id', 'point_contributions.beneficiary_id')
                            ->where('community_memberships.community_id', $community->id)
                            ->where(fn ($memberships) => CommunityMembership::applyEffectiveAccess($memberships));
                    });
            })
            ->where(function (Builder $sub) use ($community) {
                $sub->where('point_contributions.actor_id', $community->creator_id)
                    ->orWhereExists(function ($memberSub) use ($community) {
                        $memberSub->selectRaw('1')
                            ->from('community_memberships')
                            ->whereColumn('community_memberships.user_id', 'point_contributions.actor_id')
                            ->where('community_memberships.community_id', $community->id)
                            ->where(fn ($memberships) => CommunityMembership::applyEffectiveAccess($memberships));
                    });
            });

        if ($period === 'month') {
            $start = now('UTC')->startOfMonth();
            $end = now('UTC')->endOfMonth();
            $query->whereBetween('point_contributions.contributed_at', [$start, $end]);
        }

        return $query;
    }

    /**
     * Get total eligible points for a user in a community for given period.
     */
    public static function getUserPoints(Community $community, User $user, string $period = 'lifetime'): int
    {
        return (int) self::query()
            ->eligible($community, $period)
            ->where('beneficiary_id', $user->id)
            ->sum('points');
    }

    /**
     * Base query for eligible ranking population (Creator + ACTIVE members).
     *
     * @return Builder<User>
     */
    public static function eligibleUsersQuery(Community $community): Builder
    {
        return User::query()
            ->where(function (Builder $q) use ($community) {
                $q->where('users.id', $community->creator_id)
                    ->orWhereExists(function ($sub) use ($community) {
                        $sub->selectRaw('1')
                            ->from('community_memberships')
                            ->whereColumn('community_memberships.user_id', 'users.id')
                            ->where('community_memberships.community_id', $community->id)
                            ->where(fn ($memberships) => CommunityMembership::applyEffectiveAccess($memberships));
                    });
            });
    }

    /**
     * Get ranking position of a user in a community for given period.
     * Uses the exact same eligible user set and stable tie-breaking (points DESC, user_id ASC)
     * as the leaderboard table.
     * Returns null if user is not eligible (not creator and not active member).
     */
    public static function getUserRank(Community $community, User $user, string $period = 'lifetime'): ?int
    {
        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isMember) {
            return null;
        }

        $userPoints = self::getUserPoints($community, $user, $period);

        $periodPointsSubquery = self::query()
            ->eligible($community, $period)
            ->select('beneficiary_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('beneficiary_id');

        $aheadCount = self::eligibleUsersQuery($community)
            ->leftJoinSub($periodPointsSubquery, 'period_points', function ($join) {
                $join->on('users.id', '=', 'period_points.beneficiary_id');
            })
            ->where(function (Builder $q) use ($userPoints, $user) {
                $q->where(DB::raw('COALESCE(period_points.total_points, 0)'), '>', $userPoints)
                    ->orWhere(function (Builder $sub) use ($userPoints, $user) {
                        $sub->where(DB::raw('COALESCE(period_points.total_points, 0)'), '=', $userPoints)
                            ->where('users.id', '<', $user->id);
                    });
            })
            ->count();

        return $aheadCount + 1;
    }

    /**
     * Get paginated leaderboard for community and period.
     * Based on Creator + ACTIVE members, left joined with eligible points.
     * Rank is continuous across pagination and level is ALWAYS determined from lifetime points.
     */
    public static function getLeaderboard(Community $community, string $period = 'lifetime', int $perPage = 20): LengthAwarePaginator
    {
        $periodPointsSubquery = self::query()
            ->eligible($community, $period)
            ->select('beneficiary_id', DB::raw('SUM(points) as total_points'))
            ->groupBy('beneficiary_id');

        $lifetimePointsSubquery = self::query()
            ->eligible($community, 'lifetime')
            ->select('beneficiary_id', DB::raw('SUM(points) as lifetime_points'))
            ->groupBy('beneficiary_id');

        $paginated = self::eligibleUsersQuery($community)
            ->leftJoinSub($periodPointsSubquery, 'period_points', function ($join) {
                $join->on('users.id', '=', 'period_points.beneficiary_id');
            })
            ->leftJoinSub($lifetimePointsSubquery, 'lifetime_points_sub', function ($join) {
                $join->on('users.id', '=', 'lifetime_points_sub.beneficiary_id');
            })
            ->select([
                'users.id as user_id',
                'users.name',
                'users.avatar_path',
                DB::raw('COALESCE(period_points.total_points, 0) as total_points'),
                DB::raw('COALESCE(lifetime_points_sub.lifetime_points, 0) as lifetime_points'),
            ])
            ->orderByDesc('total_points')
            ->orderBy('users.id', 'asc')
            ->paginate($perPage);

        $userIds = $paginated->pluck('user_id')->all();
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'avatar_path'])->keyBy('id');

        $currentPage = $paginated->currentPage();
        $paginated->getCollection()->transform(function ($item, $index) use ($users, $currentPage, $perPage) {
            $user = $users->get($item->user_id);
            $points = (int) $item->total_points;
            $lifetimePoints = (int) $item->lifetime_points;
            $rank = ($currentPage - 1) * $perPage + $index + 1;
            // Level is ALWAYS derived from lifetime points, even when viewing monthly period
            $level = self::levelForPoints($lifetimePoints);

            return (object) [
                'user' => $user,
                'points' => $points,
                'lifetime_points' => $lifetimePoints,
                'rank' => $rank,
                'level' => $level,
            ];
        });

        return $paginated;
    }

    /**
     * Idempotently backfill point contributions from existing PostLike rows.
     * Uses database cursor chunking and re-locks/re-reads sources in mutation order
     * to avoid overwriting newer interleaved Unlike/re-like transactions.
     */
    public static function backfill(Community $community): int
    {
        $total = 0;

        PostLike::query()
            ->where('community_id', $community->id)
            ->orderBy('id')
            ->chunkById(100, function ($likesChunk) use ($community, &$total) {
                foreach ($likesChunk as $likeItem) {
                    DB::transaction(function () use ($community, $likeItem, &$total) {
                        $lockedCommunity = Community::query()->lockForUpdate()->find($community->id);
                        if (! $lockedCommunity) {
                            return;
                        }

                        $lockedLike = PostLike::query()
                            ->where('id', $likeItem->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedLike) {
                            return;
                        }

                        $lockedPost = $lockedCommunity->posts()->lockForUpdate()->find($lockedLike->post_id);
                        if (! $lockedPost || (int) $lockedPost->user_id === (int) $lockedLike->user_id) {
                            return;
                        }

                        self::updateOrCreate(
                            [
                                'source_type' => 'post_like',
                                'source_id' => $lockedLike->id,
                            ],
                            [
                                'community_id' => $lockedCommunity->id,
                                'beneficiary_id' => $lockedPost->user_id,
                                'actor_id' => $lockedLike->user_id,
                                'points' => 1,
                                'is_active' => (bool) $lockedLike->is_active,
                                'contributed_at' => $lockedLike->created_at ?? now(),
                            ]
                        );

                        $total++;
                    });
                }
            });

        return $total;
    }
}
