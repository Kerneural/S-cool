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

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'order',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'DRAFT',
        'order' => 0,
    ];

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

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function publishedLessons(): HasManyThrough
    {
        return $this->lessons()->where('lessons.status', 'PUBLISHED');
    }

    public function publishedLessonsCount(): int
    {
        return $this->lessons()
            ->where('lessons.status', 'PUBLISHED')
            ->count();
    }

    public function completedLessonsCountFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return $this->lessons()
            ->where('lessons.status', 'PUBLISHED')
            ->whereHas('progresses', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)->where('completed', true);
            })
            ->count();
    }

    public function progressPercentageFor(?User $user): int
    {
        $total = $this->publishedLessonsCount();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->completedLessonsCountFor($user);

        return (int) round(($completed / $total) * 100);
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
