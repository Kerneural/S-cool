<?php

namespace App\Models;

use App\Services\VideoEmbedService;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'video_url',
        'status',
        'order',
        'is_practical',
        'skill_label',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'DRAFT',
        'order' => 0,
        'is_practical' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_practical' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return VideoEmbedService::getEmbedUrl($this->video_url);
    }

    public function getProviderUrlAttribute(): ?string
    {
        return VideoEmbedService::getProviderUrl($this->video_url);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'PUBLISHED');
    }

    public function isPractical(): bool
    {
        return (bool) $this->is_practical;
    }

    public function proofOfWorks(): HasMany
    {
        return $this->hasMany(LessonProofOfWork::class);
    }

    public function currentProofOfWorkFor(?User $user): ?LessonProofOfWork
    {
        if (! $user) {
            return null;
        }

        if ($this->relationLoaded('proofOfWorks')) {
            return $this->proofOfWorks->first(fn ($p) => $p->user_id === $user->id && $p->is_current);
        }

        return $this->proofOfWorks()->where('user_id', $user->id)->where('is_current', true)->first();
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('progresses')) {
            return (bool) $this->progresses->firstWhere('user_id', $user->id)?->completed;
        }

        return $this->progresses()->where('user_id', $user->id)->where('completed', true)->exists();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
