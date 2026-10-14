<?php

namespace App\Models;

use App\Services\VideoEmbedService;
use Database\Factories\CommunityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Community extends Model
{
    /** @use HasFactory<CommunityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'rules',
        'intro_video_url',
        'onboarding_enabled',
        'introduction_category_id',
        'referral_commission_rate',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'onboarding_enabled' => 'boolean',
            'referral_commission_rate' => 'integer',
        ];
    }

    /**
     * Baseline defaults; access/state transitions require explicit trusted actions.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'visibility' => 'PRIVATE',
        'access_mode' => 'FREE',
        'billing_period' => 'ONE_TIME',
        'status' => 'ACTIVE',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the creator of the community.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CommunityMembership::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CommunityInvitation::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(PostCategory::class)->orderBy('name');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('order')->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ContentReport::class);
    }

    public function platformAdminActions(): HasMany
    {
        return $this->hasMany(PlatformAdminAction::class);
    }

    public function latestPlatformAdminAction(): HasOne
    {
        return $this->hasOne(PlatformAdminAction::class)->latestOfMany();
    }

    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class);
    }

    public function referralRequests(): HasMany
    {
        return $this->hasMany(ReferralInvitationRequest::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(CommunityConversation::class);
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('creator_id', $user->id)
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('status', 'ACTIVE')->whereHas('memberships', function (Builder $query) use ($user): void {
                        $query->where('user_id', $user->id)->effective();
                    });
                });
        });
    }

    /**
     * Scope a query to only include active communities.
     *
     * @param  Builder<Community>  $query
     * @return Builder<Community>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Determine if the community is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Determine if the given user is the creator of the community.
     */
    public function isCreator(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return (int) $this->creator_id === (int) $user->id;
    }

    /**
     * Determine if the given user is an active member of the community.
     */
    public function isActiveMember(?User $user): bool
    {
        if ($user === null || ! $this->isActive()) {
            return false;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->effective()
            ->exists();
    }

    /**
     * Determine if the given user has authorized access (creator or active member).
     */
    public function hasAccess(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->isActive() && ($this->isCreator($user) || $this->isActiveMember($user));
    }

    /**
     * Get the embedded player URL for the community intro video, if valid.
     */
    public function getIntroEmbedUrlAttribute(): ?string
    {
        return VideoEmbedService::getEmbedUrl($this->intro_video_url);
    }

    public function introductionCategory(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'introduction_category_id');
    }

    public function getIntroductionCategory(): ?PostCategory
    {
        if (! $this->introduction_category_id) {
            return null;
        }

        $cat = $this->introductionCategory;
        if ($cat && (int) $cat->community_id === (int) $this->id) {
            return $cat;
        }

        return null;
    }

    public function onboardingCompletions(): HasMany
    {
        return $this->hasMany(OnboardingStepCompletion::class);
    }

    public function pointContributions(): HasMany
    {
        return $this->hasMany(PointContribution::class);
    }
}
