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
        Schema::create('processed_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('provider_event_id', 128);
            $table->string('external_reference', 64)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_event_id'], 'processed_webhooks_provider_event_unique');
            $table->index(['provider', 'external_reference'], 'processed_webhooks_provider_ref_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processed_webhook_events');
    }
};
