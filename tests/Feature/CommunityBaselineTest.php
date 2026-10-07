<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommunityBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_communities_table_schema_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('communities'), 'Expected communities table does not exist.');

        $expectedColumns = [
            'id',
            'creator_id',
            'name',
            'slug',
            'description',
            'visibility',
            'access_mode',
            'status',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('communities', $column),
                "Expected column {$column} on communities table does not exist."
            );
        }
    }

    public function test_community_model_relationships_and_factory(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Laravel Builders',
            'slug' => 'laravel-builders',
            'status' => 'ACTIVE',
        ]);

        $this->assertTrue($community->isActive());
        $this->assertTrue($community->isCreator($creator));
        $this->assertSame($creator->id, $community->creator->id);
        $this->assertTrue($creator->createdCommunities->contains($community));
    }

    public function test_unauthenticated_guests_cannot_access_community_routes(): void
    {
        $community = Community::factory()->create();

        $this->get('/communities')->assertRedirect('/login');
        $this->get('/communities/create')->assertRedirect('/login');
        $this->post('/communities', ['name' => 'Test', 'slug' => 'test'])->assertRedirect('/login');
        $this->get("/communities/{$community->slug}")->assertRedirect('/login');
        $this->get("/communities/{$community->slug}/edit")->assertRedirect('/login');
        $this->put("/communities/{$community->slug}", ['name' => 'Updated'])->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_index_and_create_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/communities')
            ->assertOk()
            ->assertSee('Communities')
            ->assertSee('Create Community');

        $this->actingAs($user)
            ->get('/communities/create')
            ->assertOk()
            ->assertSee('Create Private Community');
    }

    public function test_authenticated_user_can_create_community_and_becomes_creator(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/communities', [
            'name' => 'AI Engineer Collective',
            'slug' => 'ai-engineer-collective',
            'description' => 'A private community for AI practitioners.',
        ]);

        $response->assertRedirect('/communities/ai-engineer-collective');
        $response->assertSessionHas('status', 'community-created');

        $this->assertDatabaseHas('communities', [
            'name' => 'AI Engineer Collective',
            'slug' => 'ai-engineer-collective',
            'description' => 'A private community for AI practitioners.',
            'creator_id' => $user->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_creator_id_cannot_be_spoofed_from_request_payload(): void
    {
        $user = User::factory()->create();
        $targetVictim = User::factory()->create();

        $this->actingAs($user)->post('/communities', [
            'name' => 'Spoof Attempt Community',
            'slug' => 'spoof-attempt',
            'creator_id' => $targetVictim->id,
        ]);

        $community = Community::where('slug', 'spoof-attempt')->firstOrFail();
        $this->assertSame($user->id, $community->creator_id);
        $this->assertNotSame($targetVictim->id, $community->creator_id);
    }

    public function test_slug_must_be_unique_globally(): void
    {
        $creator1 = User::factory()->create();
        $creator2 = User::factory()->create();

        Community::factory()->create([
            'creator_id' => $creator1->id,
            'slug' => 'unique-hub',
        ]);

        $response = $this->actingAs($creator2)->from('/communities/create')->post('/communities', [
            'name' => 'Another Hub with Same Slug',
            'slug' => 'unique-hub',
        ]);

        $response->assertRedirect('/communities/create');
        $response->assertSessionHasErrors(['slug']);
    }

    public function test_slug_format_is_validated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/communities/create')->post('/communities', [
            'name' => 'Invalid Slug Name',
            'slug' => 'Invalid Slug With Spaces!',
        ]);

        $response->assertRedirect('/communities/create');
        $response->assertSessionHasErrors(['slug']);
    }

    public function test_creator_can_view_own_active_community_dashboard(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Private Masterclass',
            'slug' => 'private-masterclass',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator)
            ->get("/communities/{$community->slug}")
            ->assertOk()
            ->assertSee('Private Masterclass')
            ->assertSee('private-masterclass')
            ->assertSee('Edit Settings');
    }

    public function test_non_creator_cannot_view_private_community(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();

        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'slug' => 'secret-circle',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($stranger)
            ->get("/communities/{$community->slug}")
            ->assertNotFound();
    }

    public function test_creator_cannot_view_suspended_community(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->suspended()->create([
            'creator_id' => $creator->id,
            'slug' => 'suspended-hub',
        ]);

        $this->actingAs($creator)
            ->get("/communities/{$community->slug}")
            ->assertNotFound();
    }

    public function test_creator_can_edit_and_update_community_details(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Old Name',
            'slug' => 'editable-hub',
            'description' => 'Old description',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator)
            ->get("/communities/{$community->slug}/edit")
            ->assertOk()
            ->assertSee('Edit Community')
            ->assertSee('Old Name');

        $response = $this->actingAs($creator)->put("/communities/{$community->slug}", [
            'name' => 'Updated Community Name',
            'description' => 'Updated community description.',
        ]);

        $response->assertRedirect("/communities/{$community->slug}");
        $response->assertSessionHas('status', 'community-updated');

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'name' => 'Updated Community Name',
            'description' => 'Updated community description.',
            'slug' => 'editable-hub',
        ]);
    }

    public function test_non_creator_cannot_edit_or_update_community(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();

        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Protected Community',
            'slug' => 'protected-community',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($stranger)
            ->get("/communities/{$community->slug}/edit")
            ->assertNotFound();

        $this->actingAs($stranger)
            ->put("/communities/{$community->slug}", [
                'name' => 'Hacked Name',
                'description' => 'Hacked description',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'name' => 'Protected Community',
        ]);
    }

    public function test_one_creator_can_own_multiple_communities(): void
    {
        $creator = User::factory()->create();

        foreach (['first-hub', 'second-hub'] as $slug) {
            $this->actingAs($creator)->post('/communities', [
                'name' => $slug,
                'slug' => $slug,
            ])->assertRedirect("/communities/{$slug}");
        }

        $this->assertSame(2, $creator->createdCommunities()->count());
    }

    public function test_private_and_free_defaults_are_enforced_without_request_input(): void
    {
        $creator = User::factory()->create();

        $this->actingAs($creator)->post('/communities', [
            'name' => 'Private by default',
            'slug' => 'private-default',
            'visibility' => 'PUBLIC',
            'access_mode' => 'PAID',
            'status' => 'SUSPENDED',
        ])->assertRedirect('/communities/private-default');

        $community = Community::where('slug', 'private-default')->firstOrFail();
        $this->assertSame('PRIVATE', $community->visibility);
        $this->assertSame('FREE', $community->access_mode);
        $this->assertSame('ACTIVE', $community->status);
    }

    public function test_database_defaults_and_creator_constraint_are_real(): void
    {
        $creator = User::factory()->create();
        DB::table('communities')->insert([
            'creator_id' => $creator->id,
            'name' => 'Database defaults',
            'slug' => 'db-defaults',
        ]);

        $this->assertDatabaseHas('communities', [
            'slug' => 'db-defaults',
            'visibility' => 'PRIVATE',
            'access_mode' => 'FREE',
            'status' => 'ACTIVE',
        ]);

        $this->expectException(QueryException::class);
        DB::table('communities')->insert([
            'creator_id' => $creator->id + 1000,
            'name' => 'Invalid creator',
            'slug' => 'invalid-creator',
        ]);
    }

    public function test_database_unique_constraint_rejects_duplicate_slugs(): void
    {
        Community::factory()->create(['slug' => 'duplicate-hub']);

        $this->expectException(UniqueConstraintViolationException::class);
        Community::factory()->create(['slug' => 'duplicate-hub']);
    }

    public function test_create_is_reserved_even_with_uppercase_input(): void
    {
        $creator = User::factory()->create();

        foreach (['create', 'CREATE'] as $slug) {
            $this->actingAs($creator)->from('/communities/create')->post('/communities', [
                'name' => 'Reserved route',
                'slug' => $slug,
            ])->assertRedirect('/communities/create')->assertSessionHasErrors('slug');
        }

        $this->assertDatabaseCount('communities', 0);
    }

    public function test_slug_is_normalized_before_uniqueness_validation(): void
    {
        $creator = User::factory()->create();

        $this->actingAs($creator)->post('/communities', [
            'name' => 'Normalized hub',
            'slug' => 'UPPER-HUB',
        ])->assertRedirect('/communities/upper-hub');

        $this->actingAs($creator)->from('/communities/create')->post('/communities', [
            'name' => 'Duplicate case',
            'slug' => 'Upper-Hub',
        ])->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('communities', 1);
    }

    public function test_non_ascii_slugs_are_rejected(): void
    {
        $creator = User::factory()->create();

        $this->actingAs($creator)->from('/communities/create')->post('/communities', [
            'name' => 'Invalid route slug',
            'slug' => "caf\u{00e9}-hub",
        ])->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('communities', 0);
    }

    public function test_duplicate_slug_insert_race_returns_validation_instead_of_server_error(): void
    {
        $creator = User::factory()->create();
        $otherCreator = User::factory()->create();
        $inserted = false;

        // Simulate another insert after the validator read but before this request writes.
        DB::listen(function (QueryExecuted $query) use (&$inserted, $otherCreator): void {
            if (! $inserted && str_contains($query->sql, 'count(*)')
                && str_contains($query->sql, '`communities`')
                && in_array('race-hub', $query->bindings, true)) {
                $inserted = true;
                Community::factory()->create([
                    'creator_id' => $otherCreator->id,
                    'slug' => 'race-hub',
                ]);
            }
        });

        $this->actingAs($creator)->from('/communities/create')->post('/communities', [
            'name' => 'Losing insert',
            'slug' => 'race-hub',
        ])->assertRedirect('/communities/create')->assertSessionHasErrors('slug');

        $this->assertTrue($inserted, 'The competing insert must occur during validation.');
        $this->assertDatabaseCount('communities', 1);
        $this->assertDatabaseHas('communities', ['slug' => 'race-hub', 'creator_id' => $otherCreator->id]);
    }

    public function test_existing_and_missing_private_routes_do_not_reveal_metadata(): void
    {
        $stranger = User::factory()->create();
        $community = Community::factory()->create([
            'name' => 'Secret name not for strangers',
            'slug' => 'hidden-hub',
        ]);

        foreach ([$community->slug, 'does-not-exist'] as $slug) {
            $this->actingAs($stranger)->get("/communities/{$slug}")
                ->assertNotFound()->assertDontSee($community->name);
            $this->actingAs($stranger)->get("/communities/{$slug}/edit")
                ->assertNotFound()->assertDontSee($community->name);
            $this->actingAs($stranger)->put("/communities/{$slug}", ['name' => 'Attack'])
                ->assertNotFound();
        }
    }

    public function test_community_index_is_owner_scoped_and_paginated(): void
    {
        $creator = User::factory()->create();
        Community::factory()->count(13)->create(['creator_id' => $creator->id]);
        $other = Community::factory()->create(['name' => 'Other tenant secret']);

        $first = $this->actingAs($creator)->get('/communities')->assertOk()
            ->assertDontSee($other->name)->viewData('communities');
        $this->assertSame(13, $first->total());
        $this->assertCount(12, $first->items());

        $second = $this->actingAs($creator)->get('/communities?page=2')->assertOk()
            ->assertDontSee($other->name)->viewData('communities');
        $this->assertCount(1, $second->items());
    }

    public function test_update_payload_cannot_transfer_owner_or_change_access_state(): void
    {
        $community = Community::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($community->creator)->put("/communities/{$community->slug}", [
            'name' => 'Allowed name',
            'creator_id' => $other->id,
            'slug' => 'changed-slug',
            'status' => 'SUSPENDED',
            'visibility' => 'PUBLIC',
            'access_mode' => 'PAID',
        ])->assertRedirect("/communities/{$community->slug}");

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'creator_id' => $community->creator_id,
            'slug' => $community->slug,
            'status' => 'ACTIVE',
            'visibility' => 'PRIVATE',
            'access_mode' => 'FREE',
        ]);
    }

    public function test_inactive_community_cannot_be_updated_and_is_not_linked_as_open(): void
    {
        foreach (['SUSPENDED', 'ARCHIVED'] as $status) {
            $community = Community::factory()->create(['status' => $status]);

            $this->actingAs($community->creator)->put("/communities/{$community->slug}", [
                'name' => 'Forbidden state update',
            ])->assertNotFound();
            $this->get('/communities')->assertOk()
                ->assertDontSee(route('communities.show', $community), false);
            $this->assertNotSame('Forbidden state update', $community->refresh()->name);
        }
    }

    public function test_factory_states_and_active_scope_are_consistent(): void
    {
        Community::factory()->create();
        Community::factory()->suspended()->create();
        Community::factory()->archived()->create();

        $this->assertSame(1, Community::active()->count());
        $this->assertSame(3, Community::count());
    }

    public function test_community_content_is_escaped_in_views(): void
    {
        $community = Community::factory()->create([
            'name' => '<script>alert(1)</script>',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $this->actingAs($community->creator)->get("/communities/{$community->slug}")
            ->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_profile_deletion_cannot_cascade_away_owned_communities(): void
    {
        foreach (['ACTIVE', 'SUSPENDED', 'ARCHIVED'] as $status) {
            $community = Community::factory()->create(['status' => $status]);
            $creator = $community->creator;

            $this->actingAs($creator)->from('/profile')->delete('/profile', [
                'password' => 'password',
            ])->assertRedirect('/profile')->assertSessionHasErrorsIn('userDeletion', 'password');

            $this->assertAuthenticatedAs($creator);
            $this->assertDatabaseHas('users', ['id' => $creator->id]);
            $this->assertDatabaseHas('communities', ['id' => $community->id]);
        }
    }

    public function test_database_creator_foreign_key_blocks_direct_owner_deletion(): void
    {
        $community = Community::factory()->create();
        $creator = $community->creator;

        try {
            $creator->delete();
            $this->fail('The foreign key must reject deleting a community owner.');
        } catch (QueryException $exception) {
            $this->assertSame(1451, $exception->errorInfo[1]);
        }

        $this->assertDatabaseHas('users', ['id' => $creator->id]);
        $this->assertDatabaseHas('communities', ['id' => $community->id]);
    }

    public function test_model_mass_assignment_cannot_change_ownership_or_access_state(): void
    {
        $community = Community::factory()->create();
        $other = User::factory()->create();
        $community->fill([
            'creator_id' => $other->id,
            'visibility' => 'PUBLIC',
            'access_mode' => 'PAID',
            'status' => 'SUSPENDED',
        ])->save();

        $this->assertNotSame($other->id, $community->refresh()->creator_id);
        $this->assertSame('PRIVATE', $community->visibility);
        $this->assertSame('FREE', $community->access_mode);
        $this->assertSame('ACTIVE', $community->status);
    }

    public function test_database_rejects_public_visibility(): void
    {
        $community = Community::factory()->create();

        $this->expectException(QueryException::class);
        DB::table('communities')->where('id', $community->id)->update(['visibility' => 'PUBLIC']);
    }

    public function test_create_requires_valid_name_and_description(): void
    {
        $creator = User::factory()->create();

        $this->actingAs($creator)->from('/communities/create')->post('/communities', [
            'name' => str_repeat('a', 256),
            'slug' => 'invalid-fields',
            'description' => str_repeat('a', 1001),
        ])->assertSessionHasErrors(['name', 'description']);

        $this->assertDatabaseCount('communities', 0);
    }

    public function test_malformed_slug_payloads_return_validation_errors(): void
    {
        $creator = User::factory()->create();

        foreach ([null, 123, ['not-a-string']] as $slug) {
            $this->actingAs($creator)->postJson('/communities', [
                'name' => 'Malformed slug',
                'slug' => $slug,
            ])->assertUnprocessable()->assertJsonValidationErrors('slug');
        }

        $this->assertDatabaseCount('communities', 0);
    }
}
