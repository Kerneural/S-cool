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
        Schema::table('communities', function (Blueprint $table) {
            $table->unsignedTinyInteger('referral_commission_rate')->default(0)->after('price_vnd');
        });

        Schema::table('community_invitations', function (Blueprint $table) {
            $table->foreignId('referrer_id')->nullable()->after('inviter_id')->constrained('users')->nullOnDelete();
            $table->index(['community_id', 'referrer_id']);
        });

        Schema::table('community_memberships', function (Blueprint $table) {
            $table->string('referral_code', 32)->nullable()->unique()->after('status');
        });

        Schema::create('referral_invitation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('status', 32)->default('PENDING');
            $table->foreignId('community_invitation_id')->nullable()->constrained('community_invitations')->nullOnDelete();
            $table->timestamps();

            $table->index(['community_id', 'status']);
            $table->index(['referrer_id', 'created_at']);
        });

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->restrictOnDelete();
            $table->foreignId('payment_id')->unique()->constrained('payments')->restrictOnDelete();
            $table->foreignId('referrer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('payer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('community_invitation_id')->nullable()->constrained('community_invitations')->nullOnDelete();
            $table->string('purpose', 32);
            $table->unsignedTinyInteger('commission_rate');
            $table->unsignedInteger('payment_amount');
            $table->unsignedInteger('commission_amount');
            $table->string('currency', 3)->default('VND');
            $table->string('status', 32)->default('SIMULATED_UNPAID');
            $table->timestamps();

            $table->index(['community_id', 'referrer_id']);
            $table->index(['referrer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referral_invitation_requests');

        Schema::table('community_memberships', function (Blueprint $table) {
            $table->dropColumn('referral_code');
        });

        Schema::table('community_invitations', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropIndex(['community_id', 'referrer_id']);
            $table->dropColumn('referrer_id');
        });

        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('referral_commission_rate');
        });
    }
};
