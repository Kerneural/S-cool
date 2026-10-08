<?php

namespace Tests\Feature;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommunityFeedLockingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_independent_connection_cannot_delete_parent_or_revoke_membership_during_mutation(): void
    {
        $community = Community::factory()->create();
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $post = Post::factory()->create(['community_id' => $community->id]);
        config(['database.connections.feed_contender' => config('database.connections.mysql')]);
        $contender = DB::connection('feed_contender');
        $this->assertSame('scool_test', $contender->getDatabaseName());
        $contender->statement('SET SESSION innodb_lock_wait_timeout = 1');

        try {
            app(FeedMutation::class)->run($membership->user, $community, $post->id, null, function ($current, $lockedPost) use ($contender, $membership): void {
                foreach (['posts' => $lockedPost->id, 'community_memberships' => $membership->id] as $table => $id) {
                    try {
                        $contender->table($table)->where('id', $id)->update(['updated_at' => '2026-10-08 01:00:00']);
                        $this->fail("Expected $table row to be locked against an independent connection.");
                    } catch (QueryException $exception) {
                        $this->assertSame(1205, $exception->errorInfo[1]);
                    }
                }
                $lockedPost->comments()->create(['user_id' => $membership->user_id, 'body' => 'Committed under lock']);
            });
            $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'body' => 'Committed under lock']);
        } finally {
            DB::purge('feed_contender');
        }
    }
}
