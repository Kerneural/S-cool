<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_reports', function (Blueprint $table) {
            $table->dropForeign(['reporter_id']);
            $table->unsignedBigInteger('reporter_id')->nullable()->change();
            $table->foreign('reporter_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('content_reports', function (Blueprint $table) {
            $table->dropForeign(['reporter_id']);
            $table->unsignedBigInteger('reporter_id')->nullable(false)->change();
            $table->foreign('reporter_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
