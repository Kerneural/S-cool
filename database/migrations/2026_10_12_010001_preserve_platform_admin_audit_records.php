<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Forward-only upgrade of the existing audit table; keep every record.
        Schema::table('platform_admin_actions', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['community_id']);
            $table->foreign('admin_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('community_id')->references('id')->on('communities')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('platform_admin_actions', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['community_id']);
            $table->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('community_id')->references('id')->on('communities')->cascadeOnDelete();
        });
    }
};
