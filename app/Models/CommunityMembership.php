<?php

namespace App\Models;

use App\Casts\UtcDateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityMembership extends Model
{
    use HasFactory;

    public const STATES = ['PENDING_PAYMENT', 'ACTIVE', 'SUSPENDED', 'REMOVED', 'LEFT'];

    public const ACCESS_LIFETIME = 'LIFETIME';

    public const ACCESS_TERM = 'TERM';

    public const TERM_DAYS = 30;

    protected $guarded = ['*'];

    protected $attributes = ['status' => 'PENDING_PAYMENT', 'access_kind' => self::ACCESS_LIFETIME];

    protected function casts(): array
    {
        return ['expires_at' => UtcDateTime::class];
    }

    public function hasEffectiveAccess(): bool
    {
        return $this->status === 'ACTIVE' && $this->community?->isActive()
            && ($this->access_kind === self::ACCESS_LIFETIME
                || ($this->access_kind === self::ACCESS_TERM && $this->expires_at?->greaterThan(now('UTC'))));
    }

    /** Shared SQL predicate for policies, lists, delivery and raw aggregate queries. */
    public static function applyEffectiveAccess(Builder|QueryBuilder $query, string $table = 'community_memberships'): Builder|QueryBuilder
    {
        return $query->where($table.'.status', 'ACTIVE')
            ->where(function ($query) use ($table): void {
                $query->where($table.'.access_kind', self::ACCESS_LIFETIME)
                    ->orWhere(function ($query) use ($table): void {
                        $query->where($table.'.access_kind', self::ACCESS_TERM)
                            ->where($table.'.expires_at', '>', now('UTC')->format('Y-m-d H:i:s'));
                    });
            })->whereExists(function ($community) use ($table): void {
                $community->selectRaw('1')->from('communities')
                    ->whereColumn('communities.id', $table.'.community_id')->where('communities.status', 'ACTIVE');
            });
    }

    public function scopeEffective(Builder $query): Builder
    {
        self::applyEffectiveAccess($query);

        return $query;
    }

    public function canRenew(?User $user): bool
    {
        return $user !== null && (int) $this->user_id === (int) $user->id && $user->hasVerifiedEmail()
            && $this->status === 'ACTIVE' && $this->access_kind === self::ACCESS_TERM && $this->expires_at !== null
            && $this->community?->isActive() && ! $this->community->isCreator($user)
            && $this->community->access_mode === 'PAID' && (int) $this->community->price_vnd > 0;
    }

    public function accessStateLabel(): string
    {
        if ($this->status !== 'ACTIVE') {
            return $this->status;
        }
        if (! $this->community?->isActive()) {
            return 'Community inactive';
        }

        return $this->hasEffectiveAccess() ? 'Active access' : 'Expired access';
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(CommunityMembershipAction::class, 'membership_id');
    }

    public function latestAction(): HasOne
    {
        return $this->hasOne(CommunityMembershipAction::class, 'membership_id')->latestOfMany();
    }

    // Trusted domain primitive; callers must authorize their actor separately.
    // Paid activation is deliberately unavailable until verified billing exists.
    public function transitionTo(string $target): void
    {
        DB::transaction(function () use ($target): void {
            $current = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $allowed = [
                'PENDING_PAYMENT' => [],
                'ACTIVE' => ['SUSPENDED', 'REMOVED', 'LEFT'],
                'SUSPENDED' => ['ACTIVE', 'REMOVED'],
                'REMOVED' => [],
                'LEFT' => [],
            ];
            if (! in_array($target, $allowed[$current->status] ?? [], true)) {
                throw ValidationException::withMessages(['membership' => 'This membership transition is not permitted.']);
            }
            $current->status = $target;
            $current->save();
        });
        $this->refresh();
    }

    public function getReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        $code = strtoupper(substr(hash('sha256', "ref-{$this->community_id}-{$this->user_id}-{$this->id}"), 0, 10));
        $this->forceFill(['referral_code' => $code])->saveQuietly();

        return $code;
    }
}
