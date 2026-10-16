<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'category_id',
        'course_id',
        'lesson_id',
        'user_id',
        'title',
        'body',
        'pinned_at',
        'accepted_comment_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
        ];
    }

    /**
     * Get the community that owns the post.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the category of the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * Get the course attached to the post as learning context.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lesson attached to the post as learning context.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Determine if this post is attached to a learning context (course/lesson).
     */
    public function hasLearningContext(): bool
    {
        return $this->course_id !== null;
    }

    /**
     * Determine if the post's learning context is accessible to the specified user.
     */
    public function isLearningContextAccessibleFor(?User $user): bool
    {
        if (! $this->hasLearningContext()) {
            return false;
        }

        $course = $this->course;
        if (! $course || (int) $course->community_id !== (int) $this->community_id) {
            return false;
        }

        $community = $this->community ?? $course->community;
        if (! $community || ! $community->hasAccess($user)) {
            return false;
        }
        $isCreator = $user && $community && $community->isCreator($user);

        if (! $isCreator && ! $course->hasMemberAccess($user)) {
            return false;
        }

        if ($this->lesson_id !== null) {
            $lesson = $this->lesson;
            if (! $lesson) {
                return false;
            }

            $section = $lesson->section;
            if (! $section || (int) $section->course_id !== (int) $course->id) {
                return false;
            }

            if (! $isCreator && ! $lesson->isPublished()) {
                return false;
            }
        }

        return true;
    }

    /** Published, tenant-correct context that this viewer may actually study. */
    public function scopeAccessibleLearningContext(Builder $query, User $viewer): Builder
    {
        return $query->whereHas('community', fn (Builder $q) => $q->where('status', 'ACTIVE'))
            ->whereHas('course', function (Builder $q) use ($viewer): void {
                $q->where('status', 'PUBLISHED')->whereColumn('courses.community_id', 'posts.community_id')
                    ->where(function (Builder $access) use ($viewer): void {
                        $access->whereHas('community', fn (Builder $c) => $c->where('creator_id', $viewer->id))
                            ->orWhere(function (Builder $member) use ($viewer): void {
                                $member->whereHas('community.memberships', fn (Builder $m) => $m->effective()->where('user_id', $viewer->id))
                                    ->entitledTo($viewer);
                            });
                    });
            })->where(function (Builder $q): void {
                $q->whereNull('posts.lesson_id')
                    ->orWhereHas('lesson', fn (Builder $lesson) => $lesson->where('status', 'PUBLISHED')
                        ->whereHas('section', fn (Builder $section) => $section->whereColumn('course_id', 'posts.course_id')));
            });
    }

    /** Activity must not disclose a hidden or inaccessible attached resource. */
    public function scopeVisibleActivityFor(Builder $query, User $viewer): Builder
    {
        return $query->where(function (Builder $q) use ($viewer): void {
            $q->where(fn (Builder $plain) => $plain->whereNull('posts.course_id')->whereNull('posts.lesson_id'))
                ->orWhere(fn (Builder $context) => $context->accessibleLearningContext($viewer));
        });
    }

    /** A retained accepted pointer contributes only while its author/source remain eligible. */
    public function scopeEligibleHelpfulAnswer(Builder $query): Builder
    {
        return $query->join('comments as accepted_helpful_comment', function ($join): void {
            $join->on('accepted_helpful_comment.id', '=', 'posts.accepted_comment_id')
                ->on('accepted_helpful_comment.post_id', '=', 'posts.id')
                ->whereNull('accepted_helpful_comment.deleted_at');
        })->whereHas('community', fn (Builder $q) => $q->where('status', 'ACTIVE')
            ->whereColumn('communities.creator_id', '!=', 'accepted_helpful_comment.user_id')
            ->whereHas('memberships', fn (Builder $m) => $m->effective()->whereColumn('user_id', 'accepted_helpful_comment.user_id')))
            ->whereHas('course', fn (Builder $q) => $q->where('status', 'PUBLISHED')
                ->whereColumn('courses.community_id', 'posts.community_id')
                ->where(fn (Builder $access) => $access->where('access_mode', Course::ACCESS_MODE_INCLUDED)
                    ->orWhereHas('entitlements', fn (Builder $e) => $e->whereColumn('user_id', 'accepted_helpful_comment.user_id'))))
            ->where(function (Builder $q): void {
                $q->whereNull('posts.lesson_id')
                    ->orWhereHas('lesson', fn (Builder $lesson) => $lesson->where('status', 'PUBLISHED')
                        ->whereHas('section', fn (Builder $section) => $section->whereColumn('course_id', 'posts.course_id')));
            });
    }

    /**
     * Determine if the post is currently pinned.
     */
    public function isPinned(): bool
    {
        return $this->pinned_at !== null;
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for author.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * One visible reply for feed previews, without loading a discussion thread.
     */
    public function latestVisibleComment(): HasOne
    {
        return $this->hasOne(Comment::class)->ofMany(['created_at' => 'max', 'id' => 'max']);
    }

    /**
     * Get the accepted helpful comment for the post.
     */
    public function acceptedComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'accepted_comment_id');
    }

    /**
     * Determine if this post has an accepted helpful answer.
     */
    public function hasAcceptedAnswer(): bool
    {
        return $this->accepted_comment_id !== null;
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the likes for the post.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get the active likes for the post.
     */
    public function activeLikes(): HasMany
    {
        return $this->hasMany(PostLike::class)->where('is_active', true);
    }

    /**
     * Get the bookmarks/saves for the post.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(PostBookmark::class);
    }

    /**
     * Determine if the post is liked by a given user.
     */
    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->where('user_id', $user->id)->where('is_active', true)->isNotEmpty();
        }

        return $this->activeLikes()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the post is bookmarked/saved by a given user.
     */
    public function isBookmarkedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('bookmarks')) {
            return $this->bookmarks->where('user_id', $user->id)->isNotEmpty();
        }

        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }
}
