<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_membership_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('community_id')->constrained('communities')->restrictOnDelete();
            $table->foreignId('membership_id')->constrained('community_memberships')->restrictOnDelete();
            $table->string('action', 32);
            $table->string('previous_status', 32);
            $table->string('new_status', 32);
            $table->string('reason', 500);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['community_id', 'created_at']);
            $table->index(['membership_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_membership_actions');
    }
};
