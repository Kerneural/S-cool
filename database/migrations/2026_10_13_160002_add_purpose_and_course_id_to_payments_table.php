<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('purpose', 32)->default('COMMUNITY_ACCESS')->after('community_invitation_id');
            $table->foreignId('course_id')->nullable()->after('purpose')->constrained('courses')->nullOnDelete();

            $table->index(['community_id', 'purpose', 'status']);
            $table->index(['user_id', 'purpose', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['community_id', 'purpose', 'status']);
            $table->dropIndex(['user_id', 'purpose', 'status']);
            $table->dropForeign(['course_id']);
            $table->dropColumn(['course_id', 'purpose']);
        });
    }
};
