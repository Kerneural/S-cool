<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['PENDING_PAYMENT', 'ACTIVE', 'SUSPENDED', 'REMOVED', 'LEFT'])->default('PENDING_PAYMENT');
            $table->timestamps();
            $table->unique(['community_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_memberships');
    }
};
