<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('content_format', 16)->default('plain');
            $table->string('link_url', 2048)->nullable();
            $table->string('link_title', 255)->nullable();
            $table->string('video_url', 2048)->nullable();
        });
        Schema::table('comments', function (Blueprint $table): void {
            $table->string('content_format', 16)->default('plain');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->restrictOnDelete();
        });
        Schema::create('social_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('community_id')->constrained()->restrictOnDelete();
            $table->foreignId('post_id')->constrained()->restrictOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('path');
            $table->string('name');
            $table->string('mime', 128);
            $table->unsignedInteger('size');
            $table->boolean('is_image');
            $table->timestamps();
            $table->index(['post_id', 'comment_id']);
        });
        Schema::create('post_polls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained()->restrictOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('post_poll_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_poll_id')->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->unsignedTinyInteger('position');
            $table->timestamps();
        });
        Schema::create('post_poll_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['post_poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_poll_votes');
        Schema::dropIfExists('post_poll_options');
        Schema::dropIfExists('post_polls');
        Schema::dropIfExists('social_attachments');
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('content_format');
        });
        Schema::table('posts', fn (Blueprint $table) => $table->dropColumn(['content_format', 'link_url', 'link_title', 'video_url']));
    }
};
