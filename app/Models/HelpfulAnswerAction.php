<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpfulAnswerAction extends Model
{
    public const ACTION_ACCEPTED = 'ACCEPTED';

    public const ACTION_REVOKED = 'REVOKED';

    public const ACTION_REPLACED = 'REPLACED';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'post_id',
        'comment_id',
        'author_id',
        'actor_id',
        'action',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
