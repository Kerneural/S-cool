<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the communities created by this user.
     *
     * @return HasMany<Community, $this>
     */
    public function createdCommunities(): HasMany
    {
        return $this->hasMany(Community::class, 'creator_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CommunityMembership::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function lessonProgresses(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'creator_id');
    }
}
