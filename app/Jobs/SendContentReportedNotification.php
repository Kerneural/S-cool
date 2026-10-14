<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Community;
use App\Models\ContentReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendContentReportedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $communityId,
        public int $reportId,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $community = Community::query()->find($this->communityId);
        if (! $community || ! $community->isActive()) {
            return;
        }

        $report = ContentReport::query()->where('community_id', $community->id)->find($this->reportId);
        if (! $report || ! $report->isOpen()) {
            return;
        }

        $creatorId = (int) $community->creator_id;
        if (! $creatorId) {
            return;
        }

        // Preserve already-delivered prototype notices without rewriting their read state.
        $legacyKey = "content_reported:community_{$community->id}:{$report->target_type}_{$report->target_id}";
        if (AppNotification::query()->where('dedupe_key', $legacyKey)
            ->where('community_id', $community->id)->where('type', 'content_reported')
            ->where('notifiable_type', User::class)->where('notifiable_id', $creatorId)
            ->where('data->report_id', $report->id)->exists()) {
            return;
        }

        $dedupeKey = "content_reported:report_{$report->id}:recipient_{$creatorId}";

        AppNotification::createNotification(
            userId: $creatorId,
            communityId: $community->id,
            type: 'content_reported',
            dedupeKey: $dedupeKey,
            data: [
                'report_id' => $report->id,
                'target_type' => $report->target_type,
                'target_id' => $report->target_id,
            ],
        );
    }
}
