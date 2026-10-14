<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityConversation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'immutable_datetime',
        ];
    }

    public static function canonicalParticipants(int $userId1, int $userId2): array
    {
        return [
            min($userId1, $userId2),
            max($userId1, $userId2),
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunityMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(CommunityMessage::class, 'conversation_id')->latestOfMany();
    }

    public function isParticipant(User $user): bool
    {
        return (int) $user->id === (int) $this->user_one_id || (int) $user->id === (int) $this->user_two_id;
    }

    /** Filter eligibility before pagination, previews and unread aggregates. */
    public function scopeVisibleTo(Builder $query, User $user, Community $community): Builder
    {
        $query->where('community_id', $community->id)
            ->whereHas('community', fn (Builder $query) => $query->where('status', 'ACTIVE'))
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
            });

        foreach (['user_one_id', 'user_two_id'] as $column) {
            $query->where(function (Builder $query) use ($column, $community): void {
                $query->where($column, $community->creator_id)
                    ->orWhereIn($column, CommunityMembership::query()->select('user_id')
                        ->where('community_id', $community->id)->effective());
            });
        }

        return $query;
    }

    public function getOtherParticipant(User $user): User
    {
        if ((int) $user->id === (int) $this->user_one_id) {
            return $this->userTwo;
        }

        return $this->userOne;
    }

    public function getOtherParticipantId(User $user): int
    {
        return (int) $user->id === (int) $this->user_one_id ? (int) $this->user_two_id : (int) $this->user_one_id;
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
