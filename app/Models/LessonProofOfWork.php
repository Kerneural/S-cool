<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProofOfWork extends Model
{
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_WAITING = 'WAITING';

    public const STATUS_NEEDS_REVISION = 'NEEDS_REVISION';

    public const STATUS_VERIFIED = 'VERIFIED';

    public const STATUS_REJECTED = 'REJECTED';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'course_id',
        'lesson_id',
        'user_id',
        'revision',
        'is_current',
        'status',
        'summary',
        'url',
        'attachment_path',
        'feedback',
        'reviewer_id',
        'skill_label_snapshot',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'revision' => 1,
        'is_current' => true,
        'status' => self::STATUS_DRAFT,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision' => 'integer',
            'is_current' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isNeedsRevision(): bool
    {
        return $this->status === self::STATUS_NEEDS_REVISION;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_VERIFIED, self::STATUS_REJECTED], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_WAITING => __('Waiting for feedback'),
            self::STATUS_NEEDS_REVISION => __('Needs revision'),
            self::STATUS_VERIFIED => __('Verified'),
            self::STATUS_REJECTED => __('Rejected'),
            default => $this->status,
        };
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    /** Current access, rather than a retained submission, controls private evidence. */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->whereHas('community', fn (Builder $q) => $q->where('status', 'ACTIVE'))
            ->whereHas('course', fn (Builder $q) => $q->where('status', 'PUBLISHED')
                ->whereColumn('courses.community_id', 'lesson_proof_of_works.community_id')
                ->where(fn (Builder $access) => $access->where('access_mode', Course::ACCESS_MODE_INCLUDED)
                    ->orWhereHas('entitlements', fn (Builder $entitlements) => $entitlements->whereColumn('user_id', 'lesson_proof_of_works.user_id'))))
            ->whereHas('lesson', fn (Builder $q) => $q->where('status', 'PUBLISHED')->where('is_practical', true)
                ->whereHas('section', fn (Builder $section) => $section->whereColumn('course_id', 'lesson_proof_of_works.course_id')))
            ->whereHas('user.memberships', fn (Builder $q) => $q->effective()
                ->whereColumn('community_id', 'lesson_proof_of_works.community_id'));
    }

    /**
     * Serialize new submissions even when no current row exists. Match the existing
     * classroom/progress lock order and recheck access before storage or writes.
     *
     * @return array{Community, Course, Lesson}
     */
    public static function lockAccessibleContext(Community $community, Course $course, Lesson $lesson, User $owner): array
    {
        $community = Community::query()->lockForUpdate()->findOrFail($community->id);
        $course = $community->courses()->lockForUpdate()->findOrFail($course->id);
        $section = $course->sections()->lockForUpdate()->findOrFail($lesson->course_section_id);
        $lesson = $section->lessons()->lockForUpdate()->findOrFail($lesson->id);
        $membership = $community->memberships()->where('user_id', $owner->id)->lockForUpdate()->first();
        $entitlement = $course->isOneTime()
            ? $course->entitlements()->where('user_id', $owner->id)->lockForUpdate()->first()
            : null;
        abort_unless($community->isActive() && $course->isPublished() && $lesson->isPublished()
            && $lesson->isPractical() && $membership?->hasEffectiveAccess()
            && ($course->isIncluded() || $entitlement !== null), 404);
        $course->setRelation('community', $community);
        $section->setRelation('course', $course);
        $lesson->setRelation('section', $section);

        return [$community, $course, $lesson];
    }
}
