<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\User;
use Illuminate\Console\Command;

class CheckDeploymentOwnerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scool:owner:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify deployment mode, owner account resolution, and release community ownership.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mode = config('scool.deployment_mode', 'general');

        if (! in_array($mode, ['general', 'expert'], true)) {
            $this->error("Invalid deployment mode: [{$mode}]. Mode must be 'general' or 'expert'.");

            return Command::FAILURE;
        }

        if ($mode === 'general') {
            $this->info("Deployment mode is 'general'. Open community creation and platform administrator privileges are active.");

            return Command::SUCCESS;
        }

        // Expert Mode
        $configuredEmail = config('scool.owner_email');

        if (! is_string($configuredEmail) || trim($configuredEmail) === '') {
            $this->error('SCOOL_OWNER_EMAIL is not configured or empty in expert mode.');

            return Command::FAILURE;
        }

        /** @var User|null $owner */
        $owner = User::whereRaw('LOWER(email) = ?', [strtolower(trim($configuredEmail))])->first();

        if (! $owner) {
            $this->error('Owner account not found in database for configured owner email.');

            return Command::FAILURE;
        }

        if (! $owner->hasVerifiedEmail()) {
            $this->error('Owner account exists but its email address has not been verified.');

            return Command::FAILURE;
        }

        $mismatchedCommunities = Community::query()
            ->where('status', 'ACTIVE')
            ->where('creator_id', '!=', $owner->id)
            ->get();

        if ($mismatchedCommunities->isNotEmpty()) {
            $this->error("Found {$mismatchedCommunities->count()} active release community/communities not created by the deployment owner:");

            $tableRows = $mismatchedCommunities->map(fn (Community $c): array => [
                'id' => (string) $c->id,
                'name' => (string) $c->name,
                'slug' => (string) $c->slug,
                'creator_id' => (string) $c->creator_id,
            ])->all();

            $this->table(['ID', 'Name', 'Slug', 'Creator ID'], $tableRows);

            return Command::FAILURE;
        }

        $this->info('Expert deployment mode verified. Owner account resolved and verified. All active communities belong to the deployment owner.');

        return Command::SUCCESS;
    }
}
