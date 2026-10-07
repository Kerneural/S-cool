<?php

namespace App\Models;

use Database\Factories\CommunityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Community extends Model
{
    /** @use HasFactory<CommunityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Baseline defaults; access/state transitions require explicit trusted actions.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'visibility' => 'PRIVATE',
        'access_mode' => 'FREE',
        'status' => 'ACTIVE',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the creator of the community.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Scope a query to only include active communities.
     *
     * @param  Builder<Community>  $query
     * @return Builder<Community>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Determine if the community is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Determine if the given user is the creator of the community.
     */
    public function isCreator(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return (int) $this->creator_id === (int) $user->id;
    }
}
