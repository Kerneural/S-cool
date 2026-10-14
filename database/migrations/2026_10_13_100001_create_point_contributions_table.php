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
        Schema::create('point_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id');
            $table->foreignId('beneficiary_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('points')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('contributed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['source_type', 'source_id'], 'point_contrib_source_unique');
            $table->index(['community_id', 'beneficiary_id', 'is_active', 'contributed_at'], 'point_contrib_beneficiary_idx');
            $table->index(['community_id', 'actor_id', 'is_active', 'contributed_at'], 'point_contrib_actor_idx');
            $table->index(['community_id', 'is_active', 'contributed_at'], 'point_contrib_community_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_contributions');
    }
};
