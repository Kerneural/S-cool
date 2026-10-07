<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityInvitation extends Model
{
    use HasFactory;

    public const STATES = ['PENDING', 'ACCEPTED', 'REVOKED', 'EXPIRED'];

    protected $guarded = ['*'];

    protected $hidden = ['token_hash'];

    protected $attributes = ['status' => 'PENDING'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING' && $this->expires_at->isFuture();
    }

    public function matchesToken(string $token): bool
    {
        return $this->token_hash !== null && hash_equals($this->token_hash, hash('sha256', $token));
    }
}
