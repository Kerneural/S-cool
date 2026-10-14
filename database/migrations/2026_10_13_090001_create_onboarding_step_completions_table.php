<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->boolean('onboarding_enabled')->default(true)->after('intro_video_url');
            $table->foreignId('introduction_category_id')->nullable()->after('onboarding_enabled')->constrained('post_categories')->nullOnDelete();
        });

        Schema::create('onboarding_step_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('step_key', 32);
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['community_id', 'user_id', 'step_key'], 'onboarding_completions_unique');
        });

        $communities = DB::table('communities')->get();
        foreach ($communities as $community) {
            $cat = DB::table('post_categories')
                ->where('community_id', $community->id)
                ->where('slug', 'introductions')
                ->first();
            if ($cat) {
                DB::table('communities')
                    ->where('id', $community->id)
                    ->update(['introduction_category_id' => $cat->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_step_completions');

        Schema::table('communities', function (Blueprint $table) {
            $table->dropForeign(['introduction_category_id']);
            $table->dropColumn(['onboarding_enabled', 'introduction_category_id']);
        });
    }
};
