<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('accepted_comment_id')->nullable()->after('pinned_at')->constrained('comments')->nullOnDelete();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('is_helpful')->default(false)->after('body')->index();
        });

        Schema::create('helpful_answer_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->restrictOnDelete();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 32);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['community_id', 'created_at']);
            $table->index(['post_id', 'created_at']);
            $table->index(['author_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpful_answer_actions');

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['is_helpful']);
            $table->dropColumn('is_helpful');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['accepted_comment_id']);
            $table->dropColumn('accepted_comment_id');
        });
    }
};
