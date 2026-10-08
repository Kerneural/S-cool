<?php

namespace App\Models;

use App\Casts\UtcDateTime;
use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'creator_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'timezone',
        'meeting_url',
        'status',
    ];

    /**
     * Baseline defaults.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'SCHEDULED',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => UtcDateTime::class,
            'ends_at' => UtcDateTime::class,
        ];
    }

    /**
     * Get the community that owns the event.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the creator of the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Determine if the event is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * Determine if the event is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'SCHEDULED';
    }

    /**
     * Get the start time converted to the event's stored IANA timezone.
     */
    public function localStartsAt(): Carbon
    {
        return $this->starts_at->copy()->setTimezone($this->timezone);
    }

    /**
     * Get the end time converted to the event's stored IANA timezone.
     */
    public function localEndsAt(): Carbon
    {
        return $this->ends_at->copy()->setTimezone($this->timezone);
    }
}
