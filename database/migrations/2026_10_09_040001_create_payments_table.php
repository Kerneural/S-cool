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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('external_reference', 64)->unique();
            $table->foreignId('community_id')->constrained('communities')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('community_invitation_id')->nullable()->constrained('community_invitations')->restrictOnDelete();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('VND');
            $table->enum('status', ['PENDING', 'SUCCEEDED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->string('gateway_provider', 32)->default('SEPAY_SANDBOX');
            $table->string('gateway_transaction_id', 128)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['community_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
