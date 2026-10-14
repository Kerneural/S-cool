<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FixedTermMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_forward_upgrade_preserves_legacy_rows_as_lifetime_and_migrate_rerun_is_safe(): void
    {
        $this->assertSame('scool_test', DB::connection()->getDatabaseName());
        $community = Community::factory()->create(['access_mode' => 'PAID', 'price_vnd' => 200000]);
        $user = User::factory()->create();
        $membership = CommunityMembership::forceCreate(['community_id' => $community->id, 'user_id' => $user->id, 'status' => 'ACTIVE']);
        $payment = Payment::factory()->create(['community_id' => $community->id, 'user_id' => $user->id, 'amount' => 200000]);
        $beforeMembership = Arr::except($membership->fresh()->getAttributes(), ['access_kind', 'expires_at']);
        $beforePayment = Arr::except($payment->fresh()->getAttributes(), ['membership_access_kind', 'term_days', 'community_membership_id']);
        $migration = require database_path('migrations/2026_10_13_170001_add_fixed_term_membership_access.php');
        // Reconstruct the immediately preceding schema only in the guarded test database.
        $migration->down();
        $migration->up();
        $this->assertSame($beforeMembership, Arr::except($membership->fresh()->getAttributes(), ['access_kind', 'expires_at']));
        $this->assertSame($beforePayment, Arr::except($payment->fresh()->getAttributes(), ['membership_access_kind', 'term_days', 'community_membership_id']));
        $this->assertSame('LIFETIME', $membership->fresh()->access_kind);
        $this->assertNull($membership->fresh()->expires_at);
        $this->assertSame('LIFETIME', $payment->fresh()->membership_access_kind);
        $this->assertNull($payment->fresh()->term_days);
        $community->forceFill(['billing_period' => 'TERM_30_DAYS'])->save();
        $this->assertTrue($membership->fresh()->hasEffectiveAccess());
        $this->assertSame('scool_test', DB::connection()->getDatabaseName());
        $this->assertSame(0, Artisan::call('migrate', ['--force' => true]));
        $this->assertSame($beforePayment, Arr::except($payment->fresh()->getAttributes(), ['membership_access_kind', 'term_days', 'community_membership_id']));
        $this->assertSame(1, CommunityMembership::whereKey($membership->id)->count());
    }
}
