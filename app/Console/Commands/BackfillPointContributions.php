<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\PointContribution;
use Illuminate\Console\Command;

class BackfillPointContributions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:backfill {community? : The slug or ID of the community to backfill}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill point contributions idempotently from existing active post likes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $communityArg = $this->argument('community');

        $query = Community::query();
        if ($communityArg) {
            $query->where('slug', $communityArg)->orWhere('id', $communityArg);
        }

        $communities = $query->get();

        if ($communities->isEmpty()) {
            $this->warn('No matching communities found.');

            return self::SUCCESS;
        }

        $totalBackfilled = 0;
        foreach ($communities as $community) {
            $count = PointContribution::backfill($community);
            $totalBackfilled += $count;
            $this->info("Community [{$community->name}]: processed {$count} like contribution(s).");
        }

        $this->info("Backfill complete. Total contributions processed: {$totalBackfilled}.");

        return self::SUCCESS;
    }
}
