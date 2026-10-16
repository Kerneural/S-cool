<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_proof_of_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->restrictOnDelete();
            $table->foreignId('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('revision')->default(1);
            $table->boolean('is_current')->default(true);
            // MySQL permits multiple NULLs: historical revisions do not collide.
            $table->unsignedBigInteger('current_user_id')->nullable()->storedAs('CASE WHEN is_current = 1 THEN user_id ELSE NULL END');
            $table->enum('status', ['DRAFT', 'WAITING', 'NEEDS_REVISION', 'VERIFIED', 'REJECTED'])->default('DRAFT');
            $table->text('summary')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('attachment_path', 255)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('skill_label_snapshot', 80)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['lesson_id', 'user_id', 'revision']);
            $table->unique(['lesson_id', 'current_user_id'], 'pow_one_current_per_learner');
            $table->index(['community_id', 'status']);
            $table->index(['lesson_id', 'user_id', 'is_current']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_proof_of_works');
    }
};
