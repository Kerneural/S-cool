<?php

use App\Models\Community;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('slug', 60);
            $table->timestamps();

            $table->unique(['community_id', 'slug']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('community_id')->constrained('post_categories')->nullOnDelete();
            $table->timestamp('pinned_at')->nullable()->after('body');

            $table->index(['community_id', 'category_id']);
            $table->index(['community_id', 'pinned_at']);
        });

        // Additive setup: Seed default categories for existing communities
        foreach (Community::all() as $community) {
            $defaults = [
                ['name' => 'General', 'slug' => 'general'],
                ['name' => 'Announcements', 'slug' => 'announcements'],
                ['name' => 'Questions', 'slug' => 'questions'],
                ['name' => 'Introductions', 'slug' => 'introductions'],
            ];

            foreach ($defaults as $cat) {
                DB::table('post_categories')->insertOrIgnore([
                    'community_id' => $community->id,
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['community_id', 'category_id']);
            $table->dropIndex(['community_id', 'pinned_at']);
            $table->dropColumn(['category_id', 'pinned_at']);
        });

        Schema::dropIfExists('post_categories');
    }
};
