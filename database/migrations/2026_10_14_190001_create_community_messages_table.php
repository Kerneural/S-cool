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
        Schema::create('community_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'user_one_id', 'user_two_id'], 'comm_conv_pair_unique');
            $table->index(['community_id', 'user_one_id']);
            $table->index(['community_id', 'user_two_id']);
            $table->index(['community_id', 'last_message_at']);
        });

        Schema::create('community_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('community_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('client_send_id', 64)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'sender_id', 'client_send_id'], 'comm_msg_send_unique');
            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_messages');
        Schema::dropIfExists('community_conversations');
    }
};
