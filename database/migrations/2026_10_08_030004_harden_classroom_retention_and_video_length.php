<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['courses' => 'community_id', 'course_sections' => 'course_id', 'lessons' => 'course_section_id'] as $table => $column) {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
                $blueprint->foreign($column)->references('id')->on(match ($column) {
                    'community_id' => 'communities', 'course_id' => 'courses', default => 'course_sections',
                })->restrictOnDelete();
            });
        }
        Schema::table('lessons', fn (Blueprint $table) => $table->string('video_url', 500)->nullable()->change());
    }

    public function down(): void
    {
        if (DB::table('lessons')->whereRaw('CHAR_LENGTH(video_url) > 255')->exists()) {
            throw new RuntimeException('Rollback refused: long lesson URLs would be truncated.');
        }
        Schema::table('lessons', fn (Blueprint $table) => $table->string('video_url', 255)->nullable()->change());
        foreach (['courses' => 'community_id', 'course_sections' => 'course_id', 'lessons' => 'course_section_id'] as $table => $column) {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
                $blueprint->foreign($column)->references('id')->on(match ($column) {
                    'community_id' => 'communities', 'course_id' => 'courses', default => 'course_sections',
                })->cascadeOnDelete();
            });
        }
    }
};
