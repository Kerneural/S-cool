<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    public const ACCESS_MODE_INCLUDED = 'INCLUDED';

    public const ACCESS_MODE_ONE_TIME = 'ONE_TIME';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'access_mode',
        'price',
        'status',
        'order',
        'published_revision',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'access_mode' => self::ACCESS_MODE_INCLUDED,
        'status' => 'DRAFT',
        'order' => 0,
        'published_revision' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'published_revision' => 'integer',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('order')->orderBy('id');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, CourseSection::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(CourseEntitlement::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isIncluded(): bool
    {
        return ($this->access_mode ?? self::ACCESS_MODE_INCLUDED) === self::ACCESS_MODE_INCLUDED;
    }

    public function isOneTime(): bool
    {
        return $this->access_mode === self::ACCESS_MODE_ONE_TIME;
    }

    public function formattedPrice(): string
    {
        return $this->price ? number_format($this->price, 0, ',', '.').' VND' : 'Included';
    }

    public function isEntitledFor(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->community && $this->community->isCreator($user)) {
            return true;
        }

        if ($this->isIncluded()) {
            return true;
        }

        return $this->entitlements()->where('user_id', $user->id)->exists();
    }

    public function hasMemberAccess(?User $user): bool
    {
        return $user !== null && $this->isPublished() && $this->community?->isActive()
            && $this->community->isActiveMember($user) && $this->isEntitledFor($user);
    }

    public function scopeEntitledTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('access_mode', self::ACCESS_MODE_INCLUDED)
                ->orWhereHas('entitlements', fn ($entitlements) => $entitlements->where('user_id', $user->id));
        });
    }

    public function publishedLessons(): HasManyThrough
    {
        return $this->lessons()->where('lessons.status', 'PUBLISHED');
    }

    public function publishedLessonsCount(): int
    {
        return $this->isPublished() ? $this->publishedLessons()->count() : 0;
    }

    public function completedLessonsCountFor(?User $user): int
    {
        return $this->progressSummaryFor($user)['completed'];
    }

    public function progressPercentageFor(?User $user): int
    {
        return $this->progressSummaryFor($user)['percentage'];
    }

    /** @return array{total: int, completed: int, percentage: int} */
    public function progressSummaryFor(?User $user): array
    {
        if (! $this->isPublished()) {
            return ['total' => 0, 'completed' => 0, 'percentage' => 0];
        }

        // One SQL snapshot for numerator and denominator; never load lesson bodies.
        $counts = $this->publishedLessons()->leftJoin('lesson_progresses as personal_progress', function ($join) use ($user) {
            $join->on('personal_progress.lesson_id', '=', 'lessons.id')->where('personal_progress.user_id', $user?->id);
        })->toBase()->selectRaw('COUNT(lessons.id) as total, COALESCE(SUM(CASE WHEN personal_progress.completed = 1 THEN 1 ELSE 0 END), 0) as completed')->first();
        $total = (int) $counts->total;
        $completed = (int) $counts->completed;

        return ['total' => $total, 'completed' => $completed, 'percentage' => $total ? (int) round(100 * $completed / $total) : 0];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
