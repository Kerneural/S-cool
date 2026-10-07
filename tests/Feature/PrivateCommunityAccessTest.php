<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateCommunityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('community_media');
    }

    public function test_dashboard_lists_owned_and_active_joined_communities_not_other_tenants(): void
    {
        $user = User::factory()->create();
        $own = Community::factory()->create(['creator_id' => $user->id]);
        $joined = CommunityMembership::factory()->active()->create(['user_id' => $user->id]);
        $pending = CommunityMembership::factory()->create(['user_id' => $user->id]);
        $other = Community::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($own->name)
            ->assertSee($joined->community->name)->assertDontSee($pending->community->name)->assertDontSee($other->name);
        $this->get('/communities')->assertOk()->assertSee('Role: Member');
    }

    public function test_member_read_never_grants_creator_write_or_invitation_permissions(): void
    {
        $member = CommunityMembership::factory()->active()->create();
        $community = $member->community;
        $base = '/communities/'.$community->slug;
        $this->actingAs($member->user)->get($base)->assertOk()->assertDontSee('Edit Settings')->assertDontSee('Invitations');
        $this->get($base.'/edit')->assertNotFound();
        $this->put($base, ['name' => 'Attack'])->assertNotFound();
        $this->post($base.'/cover', ['cover' => UploadedFile::fake()->image('attack.png')])->assertNotFound();
        $this->get($base.'/invitations')->assertNotFound();
        $this->post($base.'/invitations', ['email' => 'outsider@scool.local'])->assertNotFound();
        $this->assertSame($community->name, $community->refresh()->name);
    }

    public function test_every_nonactive_membership_state_and_other_tenant_are_denied(): void
    {
        $user = User::factory()->create();
        foreach (['PENDING_PAYMENT', 'SUSPENDED', 'REMOVED', 'LEFT'] as $state) {
            $membership = CommunityMembership::factory()->create(['user_id' => $user->id, 'status' => $state]);
            $this->actingAs($user)->get('/communities/'.$membership->community->slug)->assertNotFound()->assertDontSee($membership->community->name);
            $this->get('/communities/'.$membership->community->slug.'/cover')->assertNotFound();
            $this->get('/communities')->assertDontSee($membership->community->name);
        }
        $other = Community::factory()->create();
        $this->get('/communities/'.$other->slug)->assertNotFound();
        $this->get('/communities/'.$other->id)->assertNotFound();
    }

    public function test_revocation_takes_effect_on_subsequent_requests_and_ignores_selected_tenant(): void
    {
        $member = CommunityMembership::factory()->active()->create();
        $other = Community::factory()->create();
        $this->actingAs($member->user)->withSession(['current_community_id' => $other->id])
            ->get('/communities/'.$member->community->slug)->assertOk();
        $this->get('/communities/'.$other->slug)->assertNotFound();
        $member->transitionTo('SUSPENDED');
        $this->get('/communities/'.$member->community->slug)->assertNotFound();
        $this->get('/dashboard')->assertDontSee($member->community->name);
    }

    public function test_inactive_community_blocks_members_and_media_even_with_active_membership(): void
    {
        foreach (['SUSPENDED', 'ARCHIVED'] as $state) {
            $community = Community::factory()->create(['status' => $state]);
            $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
            $this->actingAs($member->user)->get('/communities/'.$community->slug)->assertNotFound();
            $this->get('/communities/'.$community->slug.'/cover')->assertNotFound();
            $this->get('/communities')->assertDontSee($community->name);
        }
    }

    public function test_private_cover_upload_delivery_and_replacement(): void
    {
        $community = Community::factory()->create();
        $base = '/communities/'.$community->slug.'/cover';
        $this->actingAs($community->creator)->get($base)->assertNotFound();
        $this->post($base, ['cover' => UploadedFile::fake()->image('untrusted-name.png')])->assertRedirect();
        $old = $community->refresh()->cover_path;
        Storage::disk('community_media')->assertExists($old);
        $this->assertFalse(str_starts_with(config('filesystems.disks.community_media.root'), config('filesystems.disks.local.root').DIRECTORY_SEPARATOR));
        $this->assertStringNotContainsString('untrusted-name', $old);
        $response = $this->get($base)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $member = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $this->actingAs($member->user)->get($base)->assertOk();
        $this->actingAs(User::factory()->create())->get($base)->assertNotFound();
        // Laravel's signed local-storage route denies unsigned public access.
        $this->get('/storage/'.$old)->assertForbidden();
        $this->get('/community-media/'.$old)->assertNotFound();
        $this->get('/storage/community-media/'.$old)->assertForbidden();
        $this->actingAs($community->creator)->post($base, ['cover' => UploadedFile::fake()->image('replacement.png')])->assertRedirect();
        Storage::disk('community_media')->assertMissing($old);
        Storage::disk('community_media')->assertExists($community->refresh()->cover_path);
    }

    public function test_invalid_oversized_and_svg_uploads_keep_the_previous_cover(): void
    {
        $community = Community::factory()->create();
        $base = '/communities/'.$community->slug.'/cover';
        $this->actingAs($community->creator)->post($base, ['cover' => UploadedFile::fake()->image('good.png')])->assertRedirect();
        $before = $community->refresh()->cover_path;
        foreach ([
            UploadedFile::fake()->image('huge.png')->size(2049),
            UploadedFile::fake()->image('too-wide.png', 4097, 10),
            UploadedFile::fake()->createWithContent('payload.png', '<?php echo "not an image";'),
            UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ] as $file) {
            $this->from('/communities/'.$community->slug.'/edit')->post($base, ['cover' => $file])->assertSessionHasErrors('cover');
            $this->assertSame($before, $community->refresh()->cover_path);
            Storage::disk('community_media')->assertExists($before);
        }
    }

    public function test_cover_path_cannot_be_injected_or_point_to_another_tenant(): void
    {
        $first = Community::factory()->create();
        $second = Community::factory()->create();
        $this->actingAs($first->creator)->put('/communities/'.$first->slug, ['name' => 'Safe', 'cover_path' => '../secret'])->assertRedirect();
        $this->assertNull($first->refresh()->cover_path);
        $first->cover_path = $second->id.'/'.str_repeat('a', 40).'.png';
        $first->save();
        $this->get('/communities/'.$first->slug.'/cover')->assertNotFound();
        Storage::disk('community_media')->put($first->cover_path, 'Other tenant file');
        $foreign = $first->cover_path;
        $this->post('/communities/'.$first->slug.'/cover', ['cover' => UploadedFile::fake()->image('new.png')])->assertRedirect();
        Storage::disk('community_media')->assertExists($foreign);
    }

    public function test_database_failure_removes_new_upload_and_preserves_previous_cover(): void
    {
        $community = Community::factory()->create();
        $url = '/communities/'.$community->slug.'/cover';
        $this->actingAs($community->creator)->post($url, ['cover' => UploadedFile::fake()->image('first.png')])->assertRedirect();
        $old = $community->refresh()->cover_path;
        Community::updating(function (): void {
            throw new \RuntimeException('Simulated cover persistence failure.');
        });
        try {
            $this->post($url, ['cover' => UploadedFile::fake()->image('replacement.png')])->assertStatus(500);
            $this->assertSame($old, $community->refresh()->cover_path);
            $this->assertSame([$old], Storage::disk('community_media')->allFiles());
        } finally {
            Community::flushEventListeners();
        }
    }

    public function test_filesystem_failure_preserves_existing_database_reference(): void
    {
        $community = Community::factory()->create();
        $url = '/communities/'.$community->slug.'/cover';
        $this->actingAs($community->creator)->post($url, ['cover' => UploadedFile::fake()->image('first.png')])->assertRedirect();
        $old = $community->refresh()->cover_path;
        Storage::shouldReceive('disk')->with('community_media')->once()->andReturn(
            \Mockery::mock(FilesystemAdapter::class, function ($mock): void {
                $mock->shouldReceive('putFile')->once()->andReturn(false);
            })
        );
        $this->post($url, ['cover' => UploadedFile::fake()->image('replacement.png')])->assertStatus(500);
        $this->assertSame($old, $community->refresh()->cover_path);
    }
}
