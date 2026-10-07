<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->restrictOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->restrictOnDelete();
            $table->string('normalized_email');
            $table->string('token_hash', 64)->nullable()->unique();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REVOKED', 'EXPIRED'])->default('PENDING');
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['community_id', 'normalized_email', 'status'], 'invitations_community_email_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_invitations');
    }
};
