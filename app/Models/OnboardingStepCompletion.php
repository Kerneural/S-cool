<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class OnboardingStepCompletion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'user_id',
        'step_key',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Community relation.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * User relation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Idempotently mark an onboarding step as completed.
     */
    public static function markStep(Community $community, User $user, string $stepKey): ?self
    {
        if (! in_array($stepKey, ['video', 'comment', 'post'], true)) {
            return null;
        }

        if (! $community->isActive()) {
            return null;
        }

        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isMember) {
            return null;
        }

        try {
            return self::firstOrCreate(
                [
                    'community_id' => $community->id,
                    'user_id' => $user->id,
                    'step_key' => $stepKey,
                ],
                [
                    'completed_at' => now(),
                ]
            );
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return self::where('community_id', $community->id)
                    ->where('user_id', $user->id)
                    ->where('step_key', $stepKey)
                    ->first();
            }

            throw $e;
        }
    }

    /**
     * Build the onboarding checklist data for a user in a community.
     * Returns null if onboarding is disabled or user is not authorized.
     *
     * @return array{steps: list<array<string, mixed>>, totalSteps: int, completedSteps: int, allCompleted: bool, percentage: int}|null
     */
    public static function getChecklist(Community $community, ?User $user): ?array
    {
        if (! $community->onboarding_enabled || $user === null) {
            return null;
        }

        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isMember) {
            return null;
        }

        $completedKeys = self::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->pluck('step_key')
            ->all();

        $completedSet = array_flip($completedKeys);

        $steps = [];

        // 1. Introduction Video Step (only included if intro_video_url is present)
        if (! empty($community->intro_video_url)) {
            $steps[] = [
                'key' => 'video',
                'title' => 'Watch Introduction Video',
                'description' => 'Watch the community welcome video and acknowledge it.',
                'completed' => isset($completedSet['video']),
                'unavailable' => false,
                'action_type' => 'video_button',
                'about_url' => route('communities.show', $community),
            ];
        }

        // 2. First Qualifying Comment Step (always included)
        $steps[] = [
            'key' => 'comment',
            'title' => 'Leave Your First Comment',
            'description' => 'Join the discussion by commenting on any post in the community.',
            'completed' => isset($completedSet['comment']),
            'unavailable' => false,
            'action_type' => 'link',
            'action_url' => route('communities.posts.index', $community),
        ];

        // 3. First Introduction Post Step
        $introCat = $community->getIntroductionCategory();
        if ($introCat) {
            $steps[] = [
                'key' => 'post',
                'title' => 'Introduce Yourself',
                'description' => "Share a post in the '{$introCat->name}' category to introduce yourself.",
                'completed' => isset($completedSet['post']),
                'unavailable' => false,
                'category_id' => $introCat->id,
                'category_name' => $introCat->name,
                'action_type' => 'link',
                'action_url' => route('communities.posts.index', [$community, 'category' => $introCat->slug]),
            ];
        } else {
            // Category deleted: display unavailable task and exclude it from denominator
            $steps[] = [
                'key' => 'post',
                'title' => 'Introduce Yourself (Category Unavailable)',
                'description' => 'The introductions category was removed. Awaiting community creator to assign a replacement.',
                'completed' => isset($completedSet['post']),
                'unavailable' => true,
                'action_type' => 'none',
            ];
        }

        $activeSteps = array_values(array_filter($steps, fn ($s) => ! ($s['unavailable'] ?? false)));
        $totalSteps = count($activeSteps);
        $completedSteps = count(array_filter($activeSteps, fn ($s) => $s['completed']));
        $allCompleted = $totalSteps > 0 && $completedSteps === $totalSteps;
        $percentage = $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0;

        return [
            'steps' => $steps,
            'totalSteps' => $totalSteps,
            'completedSteps' => $completedSteps,
            'allCompleted' => $allCompleted,
            'percentage' => $percentage,
            'isCreator' => $isCreator,
        ];
    }
}
