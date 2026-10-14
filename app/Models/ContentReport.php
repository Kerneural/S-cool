<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentReport extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'OPEN';

    public const STATUS_RESOLVED = 'RESOLVED';

    public const STATUS_DISMISSED = 'DISMISSED';

    public const TARGET_POST = 'POST';

    public const TARGET_COMMENT = 'COMMENT';

    public const ACTION_REMOVED = 'REMOVED';

    public const ACTION_DISMISSED = 'DISMISSED';

    protected $fillable = [
        'community_id',
        'reporter_id',
        'target_type',
        'target_id',
        'reason',
        'status',
        'resolution_action',
        'resolution_reason',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isDismissed(): bool
    {
        return $this->status === self::STATUS_DISMISSED;
    }

    /**
     * Safely resolve the target entity (Post or Comment) including soft-deleted instances.
     */
    public function getTargetModel(): Post|Comment|null
    {
        return match ($this->target_type) {
            self::TARGET_POST => Post::withTrashed()->find($this->target_id),
            self::TARGET_COMMENT => Comment::withTrashed()->find($this->target_id),
            default => null,
        };
    }
}
