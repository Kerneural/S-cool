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
        Schema::table('communities', function (Blueprint $table): void {
            $table->text('rules')->nullable()->after('description');
            $table->string('intro_video_url', 500)->nullable()->after('rules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table): void {
            $table->dropColumn(['rules', 'intro_video_url']);
        });
    }
};
