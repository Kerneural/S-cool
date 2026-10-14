<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'community_id',
        'name',
        'slug',
    ];

    /**
     * Get the community that owns the category.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the posts in this category.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    /**
     * Determine if this category is the special announcements category.
     */
    public function isAnnouncements(): bool
    {
        return $this->slug === 'announcements';
    }

    /**
     * Seed default categories for a community in an idempotent fashion without overriding introduction settings.
     */
    public static function seedDefaultsFor(Community $community): void
    {
        $defaults = [
            ['name' => 'General', 'slug' => 'general'],
            ['name' => 'Announcements', 'slug' => 'announcements'],
            ['name' => 'Questions', 'slug' => 'questions'],
            ['name' => 'Introductions', 'slug' => 'introductions'],
        ];

        foreach ($defaults as $cat) {
            self::firstOrCreate(
                ['community_id' => $community->id, 'slug' => $cat['slug']],
                ['name' => $cat['name']]
            );
        }
    }

    /**
     * Initialize default categories and assign initial introduction category on community creation.
     */
    public static function initializeDefaultsFor(Community $community): void
    {
        self::seedDefaultsFor($community);

        if (! $community->introduction_category_id) {
            $introCat = self::where('community_id', $community->id)->where('slug', 'introductions')->first();
            if ($introCat) {
                $community->update(['introduction_category_id' => $introCat->id]);
            }
        }
    }
}
