<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('course_id')
                ->nullable()
                ->after('category_id')
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('lesson_id')
                ->nullable()
                ->after('course_id')
                ->constrained('lessons')
                ->nullOnDelete();

            $table->index(['community_id', 'course_id']);
            $table->index(['community_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['lesson_id']);
            $table->dropIndex(['community_id', 'course_id']);
            $table->dropIndex(['community_id', 'lesson_id']);
            $table->dropColumn(['course_id', 'lesson_id']);
        });
    }
};
