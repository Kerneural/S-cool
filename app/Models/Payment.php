<?php

namespace App\Models;

use App\Casts\UtcDateTime;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_SUCCEEDED = 'SUCCEEDED';

    public const STATUS_FAILED = 'FAILED';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const PROVIDER_SEPAY_SANDBOX = 'SEPAY_SANDBOX';

    public const PURPOSE_COMMUNITY_ACCESS = 'COMMUNITY_ACCESS';

    public const PURPOSE_COMMUNITY_JOIN = 'COMMUNITY_JOIN';

    public const PURPOSE_COURSE_ACCESS = 'COURSE_ACCESS';

    public const PURPOSE_MEMBERSHIP_RENEWAL = 'MEMBERSHIP_RENEWAL';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'external_reference',
        'community_id',
        'user_id',
        'community_invitation_id',
        'purpose',
        'course_id',
        'community_membership_id',
        'membership_access_kind',
        'term_days',
        'amount',
        'currency',
        'status',
        'gateway_provider',
        'gateway_transaction_id',
        'paid_at',
        'payload',
    ];

    /**
     * Baseline defaults.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'currency' => 'VND',
        'status' => self::STATUS_PENDING,
        'gateway_provider' => self::PROVIDER_SEPAY_SANDBOX,
        'purpose' => self::PURPOSE_COMMUNITY_ACCESS,
        'membership_access_kind' => CommunityMembership::ACCESS_LIFETIME,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'term_days' => 'integer',
            'paid_at' => UtcDateTime::class,
            'payload' => 'array',
        ];
    }

    /**
     * Get the community associated with this payment.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the user who made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invitation linked to this payment, if any.
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(CommunityInvitation::class, 'community_invitation_id');
    }

    /**
     * Get the course linked to this payment, if any.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(CommunityMembership::class, 'community_membership_id');
    }

    public function isMembershipRenewal(): bool
    {
        return $this->purpose === self::PURPOSE_MEMBERSHIP_RENEWAL;
    }

    public function entitlement(): HasOne
    {
        return $this->hasOne(CourseEntitlement::class);
    }

    public function referralCommission(): HasOne
    {
        return $this->hasOne(ReferralCommission::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCourseAccess(): bool
    {
        return $this->purpose === self::PURPOSE_COURSE_ACCESS;
    }

    public function isCommunityAccess(): bool
    {
        return in_array($this->purpose ?? self::PURPOSE_COMMUNITY_ACCESS, [self::PURPOSE_COMMUNITY_ACCESS, self::PURPOSE_COMMUNITY_JOIN], true);
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount, 0, ',', '.').' '.$this->currency;
    }

    public function grantsCurrentAccess(?User $user): bool
    {
        if (! $this->isSucceeded() || ! $user || (int) $this->user_id !== (int) $user->id) {
            return false;
        }

        return $this->isCourseAccess()
            ? (bool) $this->course?->hasMemberAccess($user)
            : (bool) ($this->community?->isActive() && $this->community->isActiveMember($user));
    }

    public function purposeLabel(): string
    {
        if ($this->isCourseAccess()) {
            return 'Course Access: '.($this->course?->title ?? 'Course #'.$this->course_id);
        }

        return $this->isMembershipRenewal() ? 'Membership Renewal (30 UTC days)'
            : ($this->membership_access_kind === CommunityMembership::ACCESS_TERM ? 'Community Admission (30 UTC days)' : 'Community Admission');
    }

    /**
     * Determine if this pending payment can currently be resumed for checkout.
     * Enforces active community, valid non-expired/non-revoked invitation, and eligible membership state.
     */
    public function canResumeCheckout(?User $user = null): bool
    {
        $user = $user ?? $this->user;

        if (! $this->isPending() || ! $this->community || ! $this->community->isActive()) {
            return false;
        }

        if ($user && $this->community->isCreator($user)) {
            return false;
        }

        if ($this->isMembershipRenewal()) {
            return $user !== null && (int) $this->user_id === (int) $user->id
                && $this->term_days === CommunityMembership::TERM_DAYS
                && $this->membership_access_kind === CommunityMembership::ACCESS_TERM
                && $this->membership && (int) $this->membership->community_id === (int) $this->community_id
                && $this->membership->canRenew($user);
        }

        if ($this->isCourseAccess()) {
            $course = $this->course;
            if (! $course || ! $course->isPublished() || ! $course->isOneTime()) {
                return false;
            }

            if (! $user) {
                return false;
            }

            $membership = $this->community->memberships()->where('user_id', $user->id)->first();
            if (! $membership || ! $membership->hasEffectiveAccess()) {
                return false;
            }

            if ($course->entitlements()->where('user_id', $user->id)->exists()) {
                return false;
            }

            return true;
        }

        if ($this->purpose === self::PURPOSE_COMMUNITY_JOIN) {
            return $user !== null && $user->hasVerifiedEmail() && (int) $this->user_id === (int) $user->id
                && $this->community->access_mode === 'PAID' && $this->community_invitation_id === null
                && $this->membership && (int) $this->membership->community_id === (int) $this->community_id
                && (int) $this->membership->user_id === (int) $user->id && $this->membership->status === 'PENDING_PAYMENT';
        }

        if ($this->community_invitation_id) {
            $invitation = $this->invitation;
            if (! $invitation || ! $invitation->isPending()) {
                return false;
            }
        }

        if ($user) {
            $membership = $this->community->memberships()->where('user_id', $user->id)->first();
            if ($membership && in_array($membership->status, ['SUSPENDED', 'REMOVED', 'ACTIVE'], true)) {
                return false;
            }
        }

        return true;
    }
}
