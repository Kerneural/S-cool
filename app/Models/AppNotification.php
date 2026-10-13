<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class AppNotification extends DatabaseNotification
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'community_id',
        'dedupe_key',
        'data',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Evaluate fresh visibility and authorization at query/render time (AC-04).
     * Prevents leakage of content or metadata if community, post, comment, course,
     * or membership is removed, unpublished, deleted, or suspended.
     */
    public function isAvailableFor(User $viewer): bool
    {
        $community = $this->community;
        if (! $community || ! $community->isActive()) {
            return false;
        }

        $isCreator = $community->isCreator($viewer);
        $isActiveMember = $community->memberships()
            ->where('user_id', $viewer->id)
            ->where('status', 'ACTIVE')
            ->exists();

        if (! $isCreator && ! $isActiveMember) {
            return false;
        }

        $data = $this->data ?? [];

        return match ($this->type) {
            'post_reply' => $this->isPostReplyAvailable($community),
            'member_activated' => $this->isMemberActivatedAvailable($community, $isCreator),
            'course_published' => $this->isCoursePublishedAvailable($community),
            default => false,
        };
    }

    protected function isPostReplyAvailable(Community $community): bool
    {
        $postId = $this->data['post_id'] ?? null;
        $commentId = $this->data['comment_id'] ?? null;

        if (! $postId || ! $commentId) {
            return false;
        }

        $post = Post::query()
            ->where('id', $postId)
            ->where('community_id', $community->id)
            ->first();

        if (! $post || $post->trashed()) {
            return false;
        }

        $comment = Comment::query()
            ->where('id', $commentId)
            ->where('post_id', $postId)
            ->first();

        if (! $comment || $comment->trashed()) {
            return false;
        }

        $commenterId = $this->data['commenter_id'] ?? null;
        if (! $commenterId || ! User::where('id', $commenterId)->exists()) {
            return false;
        }

        return true;
    }

    protected function isMemberActivatedAvailable(Community $community, bool $isCreator): bool
    {
        if (! $isCreator) {
            return false;
        }

        $membershipId = $this->data['membership_id'] ?? null;
        if (! $membershipId) {
            return false;
        }

        $membership = CommunityMembership::query()
            ->where('id', $membershipId)
            ->where('community_id', $community->id)
            ->where('status', 'ACTIVE')
            ->first();

        if (! $membership) {
            return false;
        }

        $memberId = $this->data['member_id'] ?? null;
        if (! $memberId || ! User::where('id', $memberId)->exists()) {
            return false;
        }

        return true;
    }

    protected function isCoursePublishedAvailable(Community $community): bool
    {
        $courseId = $this->data['course_id'] ?? null;
        if (! $courseId) {
            return false;
        }

        $course = Course::query()
            ->where('id', $courseId)
            ->where('community_id', $community->id)
            ->where('status', 'PUBLISHED')
            ->first();

        return $course !== null;
    }

    /**
     * Render safe display summary from fresh records.
     */
    public function summary(): string
    {
        return match ($this->type) {
            'post_reply' => $this->postReplySummary(),
            'member_activated' => $this->memberActivatedSummary(),
            'course_published' => $this->coursePublishedSummary(),
            default => 'You have a new notification.',
        };
    }

    protected function postReplySummary(): string
    {
        $commenter = User::find($this->data['commenter_id'] ?? null);
        $name = $commenter?->name ?? 'A member';

        return "{$name} replied to your post.";
    }

    protected function memberActivatedSummary(): string
    {
        $member = User::find($this->data['member_id'] ?? null);
        $name = $member?->name ?? 'A new member';

        return "{$name} joined the community.";
    }

    protected function coursePublishedSummary(): string
    {
        $course = Course::find($this->data['course_id'] ?? null);
        $title = $course?->title ?? 'New course';

        return "A new course was published: {$title}.";
    }

    /**
     * Get safe target URL or null if destination is unavailable.
     */
    public function targetUrl(Community $community): ?string
    {
        return match ($this->type) {
            'post_reply' => route('communities.posts.show', [$community, $this->data['post_id'] ?? 0]),
            'member_activated' => route('communities.members.index', $community),
            'course_published' => route('communities.classroom.index', $community),
            default => null,
        };
    }

    /**
     * Safe idempotent notification creation with dedupe_key uniqueness constraint.
     */
    public static function createNotification(
        int $userId,
        int $communityId,
        string $type,
        string $dedupeKey,
        array $data,
    ): ?self {
        try {
            return self::create([
                'id' => (string) Str::uuid(),
                'type' => $type,
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'community_id' => $communityId,
                'dedupe_key' => $dedupeKey,
                'data' => $data,
                'read_at' => null,
            ]);
        } catch (UniqueConstraintViolationException|QueryException $e) {
            return self::where('dedupe_key', $dedupeKey)->first();
        }
    }

    /**
     * Count visible unread notifications for user in community (AC-03, AC-04).
     */
    public static function unreadCountFor(User $user, Community $community): int
    {
        $unread = self::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('community_id', $community->id)
            ->whereNull('read_at')
            ->get();

        return $unread->filter(fn ($notification) => $notification->isAvailableFor($user))->count();
    }
}
