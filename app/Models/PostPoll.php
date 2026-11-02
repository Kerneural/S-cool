<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostPoll extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['closed_at' => 'datetime'];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PostPollOption::class)->orderBy('position');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PostPollVote::class);
    }
}
