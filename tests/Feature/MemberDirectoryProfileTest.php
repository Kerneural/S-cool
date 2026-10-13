<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberDirectoryProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_directory_lists_active_members_and_creator_with_roles_and_search(): void
    {
        $creator = User::factory()->create([
            'name' => 'Alice Creator',
            'email' => 'alice@secret-domain.org',
            'bio' => 'Building creative tools',
        ]);
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Art Studio',
            'slug' => 'art-studio',
            'status' => 'ACTIVE',
        ]);

        $activeMember = User::factory()->create([
            'name' => 'Bob Painter',
            'email' => 'bob@secret-domain.org',
            'bio' => 'Watercolor specialist',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $activeMember->id,
            'status' => 'ACTIVE',
        ]);

        $suspendedMember = User::factory()->create([
            'name' => 'Charlie Troublemaker',
            'email' => 'charlie@secret-domain.org',
        ]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedMember->id,
            'status' => 'SUSPENDED',
        ]);

        $outsider = User::factory()->create([
            'name' => 'David Outsider',
            'email' => 'david@secret-domain.org',
        ]);

        $this->actingAs($activeMember);

        $response = $this->get(route('communities.directory.index', $community));
        $response->assertOk()
            ->assertSee('Alice Creator')
            ->assertSee('Creator')
            ->assertSee('Building creative tools')
            ->assertSee('Bob Painter')
            ->assertSee('Member')
            ->assertSee('Watercolor specialist')
            ->assertDontSee('Charlie Troublemaker')
            ->assertDontSee('David Outsider')
            // Non-disclosure: Fellow members' and creator's emails must NEVER appear in directory HTML
            ->assertDontSee('alice@secret-domain.org')
            ->assertDontSee('charlie@secret-domain.org')
            ->assertDontSee('david@secret-domain.org');

        // Search: ?q=Bob
        $searchResponse = $this->get(route('communities.directory.index', [$community, 'q' => 'Bob']));
        $searchResponse->assertOk()
            ->assertSee('Bob Painter')
            ->assertDontSee('Alice Creator');
    }

    public function test_member_profile_view_shows_community_profile_and_returns_404_for_foreign_user(): void
    {
        $creator = User::factory()->create([
            'name' => 'Elena Director',
            'email' => 'elena@director-studio.org',
            'bio' => 'Director of Photography',
        ]);
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'name' => 'Cinema Club',
            'slug' => 'cinema-club',
            'status' => 'ACTIVE',
        ]);

        $member = User::factory()->create([
            'name' => 'Frank Actor',
            'email' => 'frank@actor-agency.org',
            'bio' => 'Method acting enthusiast',
        ]);
        $membership = CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ]);

        $foreignUser = User::factory()->create([
            'name' => 'George Unrelated',
            'email' => 'george@unrelated.org',
            'bio' => 'I belong to another community',
        ]);

        // When creator views member's profile
        $this->actingAs($creator);

        $memberProfileResponse = $this->get(route('communities.directory.show', [$community, $member]));
        $memberProfileResponse->assertOk()
            ->assertSee('Frank Actor')
            ->assertSee('Community Member')
            ->assertSee('Method acting enthusiast')
            ->assertSee('Joined '.$membership->created_at->format('M d, Y'))
            // Non-disclosure: Target member's email must NEVER appear on profile page
            ->assertDontSee('frank@actor-agency.org');

        // When member views creator's profile
        $this->actingAs($member);

        $creatorProfileResponse = $this->get(route('communities.directory.show', [$community, $creator]));
        $creatorProfileResponse->assertOk()
            ->assertSee('Elena Director')
            ->assertSee('Community Creator')
            ->assertSee('Director of Photography')
            // Non-disclosure: Target creator's email must NEVER appear on profile page
            ->assertDontSee('elena@director-studio.org');

        // Attempt to view foreign user profile in this community context -> 404 (non-disclosure)
        $this->get(route('communities.directory.show', [$community, $foreignUser]))
            ->assertNotFound();
    }

    public function test_user_can_update_bio_and_upload_valid_avatar_with_validation_guards(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'bio' => 'Initial bio',
        ]);

        $this->actingAs($user);

        // 1. Valid update with bio and avatar file (PNG binary)
        $avatarFile = UploadedFile::fake()->create('custom_avatar.png', 100, 'image/png');
        $response = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => 'Updated professional bio with valid length',
            'avatar' => $avatarFile,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('Updated professional bio with valid length', $user->bio);
        $this->assertNotNull($user->avatar_path);
        Storage::disk('local')->assertExists($user->avatar_path);

        // 2. Reject bio longer than 500 characters
        $oversizeBio = str_repeat('a', 501);
        $rejectBioResponse = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'bio' => $oversizeBio,
        ]);
        $rejectBioResponse->assertSessionHasErrors('bio');

        // 3. Reject invalid file type (e.g. text file instead of image)
        $invalidFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        $rejectFileResponse = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $invalidFile,
        ]);
        $rejectFileResponse->assertSessionHasErrors('avatar');

        // 4. Reject file larger than 2MB (2048 KB)
        $largeFile = UploadedFile::fake()->create('huge.png', 2049, 'image/png');
        $rejectLargeResponse = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $largeFile,
        ]);
        $rejectLargeResponse->assertSessionHasErrors('avatar');
    }

    public function test_avatar_route_serves_avatar_privately_and_handles_placeholder(): void
    {
        Storage::fake('local');

        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $memberWithAvatar = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $memberWithAvatar->id,
            'status' => 'ACTIVE',
        ]);

        // Put a fake avatar on private disk
        $avatarPath = 'avatars/test_avatar.png';
        Storage::disk('local')->put($avatarPath, 'binary-image-data-test');
        $memberWithAvatar->update(['avatar_path' => $avatarPath]);

        $memberWithoutAvatar = User::factory()->create(['avatar_path' => null]);
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $memberWithoutAvatar->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator);

        // Member with uploaded avatar returns file stream with private cache
        $response = $this->get(route('communities.directory.avatar', [$community, $memberWithAvatar]));
        $response->assertOk();
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));

        // Member without avatar returns SVG placeholder
        $placeholderResponse = $this->get(route('communities.directory.avatar', [$community, $memberWithoutAvatar]));
        $placeholderResponse->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false);
        $this->assertStringContainsString('private', (string) $placeholderResponse->headers->get('Cache-Control'));

        // Auth profile avatar route for current user
        $ownAvatarResponse = $this->get(route('profile.avatar'));
        $ownAvatarResponse->assertOk();
        $this->assertStringContainsString('private', (string) $ownAvatarResponse->headers->get('Cache-Control'));
    }

    public function test_non_member_and_suspended_member_cannot_access_directory_or_profiles(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $activeMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $activeMember->id,
            'status' => 'ACTIVE',
        ]);

        $suspendedMember = User::factory()->create();
        CommunityMembership::factory()->create([
            'community_id' => $community->id,
            'user_id' => $suspendedMember->id,
            'status' => 'SUSPENDED',
        ]);

        $outsider = User::factory()->create();

        // 1. Unauthenticated guest -> redirected to login
        $this->get(route('communities.directory.index', $community))
            ->assertRedirect(route('login'));

        // 2. Outsider -> 404
        $this->actingAs($outsider);
        $this->get(route('communities.directory.index', $community))->assertNotFound();
        $this->get(route('communities.directory.show', [$community, $activeMember]))->assertNotFound();
        $this->get(route('communities.directory.avatar', [$community, $activeMember]))->assertNotFound();

        // 3. Suspended member -> 404
        $this->actingAs($suspendedMember);
        $this->get(route('communities.directory.index', $community))->assertNotFound();
        $this->get(route('communities.directory.show', [$community, $activeMember]))->assertNotFound();
    }

    public function test_avatar_upload_rolls_back_and_cleans_new_file_if_database_update_fails(): void
    {
        Storage::fake('local');

        $oldAvatarPath = 'avatars/original_avatar.png';
        Storage::disk('local')->put($oldAvatarPath, 'original-avatar-content');

        $user = User::factory()->create([
            'avatar_path' => $oldAvatarPath,
        ]);

        $this->actingAs($user);

        // Simulate an unexpected database failure during transaction
        User::updating(function (): void {
            throw new \RuntimeException('Simulated database failure during profile update.');
        });

        $newFile = UploadedFile::fake()->create('new_avatar.png', 100, 'image/png');

        $this->withoutExceptionHandling();
        $thrown = false;

        try {
            $this->patch(route('profile.update'), [
                'name' => 'Attempted Change',
                'email' => $user->email,
                'avatar' => $newFile,
            ]);
        } catch (\RuntimeException $e) {
            $thrown = true;
            $this->assertSame('Simulated database failure during profile update.', $e->getMessage());
        }

        $this->assertTrue($thrown, 'Expected RuntimeException was not thrown.');

        // Original avatar must remain preserved on disk
        Storage::disk('local')->assertExists($oldAvatarPath);

        // Database user state must remain unchanged
        $user->refresh();
        $this->assertSame($oldAvatarPath, $user->avatar_path);
        $this->assertNotSame('Attempted Change', $user->name);

        // Any newly written files in avatars directory must have been cleaned up
        $storedFiles = Storage::disk('local')->files('avatars');
        $this->assertCount(1, $storedFiles);
        $this->assertSame($oldAvatarPath, $storedFiles[0]);
    }

    public function test_avatar_response_contains_strict_cache_and_security_headers_and_guards_against_traversal(): void
    {
        Storage::fake('local');

        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $validPath = 'avatars/real_avatar.png';
        Storage::disk('local')->put($validPath, 'image-content');
        $creator->update(['avatar_path' => $validPath]);

        $this->actingAs($creator);

        // 1. Valid avatar response headers on directory avatar route
        $response = $this->get(route('communities.directory.avatar', [$community, $creator]));
        $response->assertOk();
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);

        // 2. Profile avatar route headers
        $profileResponse = $this->get(route('profile.avatar'));
        $profileResponse->assertOk();
        $this->assertSame('nosniff', $profileResponse->headers->get('X-Content-Type-Options'));
        $profileCache = (string) $profileResponse->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $profileCache);
        $this->assertStringContainsString('no-store', $profileCache);

        // 3. Path traversal or invalid format falls back to SVG placeholder without exposing files
        $creator->update(['avatar_path' => 'avatars/../../etc/passwd']);
        $fallbackResponse = $this->get(route('communities.directory.avatar', [$community, $creator]));
        $fallbackResponse->assertOk();
        $this->assertSame('image/svg+xml', $fallbackResponse->headers->get('Content-Type'));
        $this->assertSame('nosniff', $fallbackResponse->headers->get('X-Content-Type-Options'));
        $fallbackCache = (string) $fallbackResponse->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $fallbackCache);
        $this->assertStringContainsString('no-store', $fallbackCache);
    }

    public function test_member_directory_search_safely_handles_array_and_oversize_search_input(): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create([
            'creator_id' => $creator->id,
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator);

        // Array search query: ?q[]=hack
        $arrayResponse = $this->get(route('communities.directory.index', [$community, 'q' => ['malicious', 'array']]));
        $arrayResponse->assertOk();

        // Oversize search query: 200 characters
        $oversizeResponse = $this->get(route('communities.directory.index', [$community, 'q' => str_repeat('z', 200)]));
        $oversizeResponse->assertOk();
    }
}
