<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 32); // 'POST' or 'COMMENT'
            $table->unsignedBigInteger('target_id');
            $table->text('reason');
            $table->string('status', 32)->default('OPEN'); // 'OPEN', 'RESOLVED', 'DISMISSED'
            $table->string('resolution_action', 32)->nullable(); // 'REMOVED', 'DISMISSED'
            $table->text('resolution_reason')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'reporter_id', 'target_type', 'target_id'], 'content_reports_unique_target');
            $table->index(['community_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_reports');
    }
};
