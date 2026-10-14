<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table): void {
            $table->enum('billing_period', ['ONE_TIME', 'TERM_30_DAYS'])->default('ONE_TIME');
        });
        Schema::table('community_memberships', function (Blueprint $table): void {
            // Explicitly grandfather every existing membership; never infer from current pricing.
            $table->enum('access_kind', ['LIFETIME', 'TERM'])->default('LIFETIME');
            $table->timestamp('expires_at')->nullable();
            $table->index(['community_id', 'status', 'access_kind', 'expires_at'], 'memberships_effective_access_index');
        });
        Schema::table('payments', function (Blueprint $table): void {
            // Legacy attempts keep their original one-time admission semantics.
            $table->enum('membership_access_kind', ['LIFETIME', 'TERM'])->default('LIFETIME');
            $table->unsignedSmallInteger('term_days')->nullable();
            $table->foreignId('community_membership_id')->nullable()->constrained('community_memberships')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('community_membership_id');
            $table->dropColumn(['membership_access_kind', 'term_days']);
        });
        Schema::table('community_memberships', function (Blueprint $table): void {
            $table->dropIndex('memberships_effective_access_index');
            $table->dropColumn(['access_kind', 'expires_at']);
        });
        Schema::table('communities', fn (Blueprint $table) => $table->dropColumn('billing_period'));
    }
};
