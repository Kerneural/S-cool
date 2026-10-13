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
        if (! Schema::hasColumn('courses', 'published_revision')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedInteger('published_revision')->default(0)->after('status');
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
                $table->string('dedupe_key')->unique();
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['community_id', 'notifiable_id', 'read_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');

        if (Schema::hasColumn('courses', 'published_revision')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('published_revision');
            });
        }
    }
};
