<?php

namespace App\Jobs;

use App\Mail\CommunityInvitationMail;
use App\Models\Community;
use App\Models\CommunityInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendCommunityInvitation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $invitationId) {}

    public function backoff(): array
    {
        return [5, 15];
    }

    public function handle(): void
    {
        try {
            $tenantId = CommunityInvitation::query()->whereKey($this->invitationId)->value('community_id');
            if ($tenantId === null) {
                return;
            }
            DB::transaction(function () use ($tenantId): void {
                $community = Community::query()->whereKey($tenantId)->lockForUpdate()->firstOrFail();
                $invite = $community->invitations()->whereKey($this->invitationId)->lockForUpdate()->firstOrFail();
                if (! $community->isActive() || ! $invite->isPending() || $invite->sent_at !== null) {
                    return;
                }
                $token = bin2hex(random_bytes(32));
                $invite->token_hash = hash('sha256', $token);
                config(['mail.mailers.smtp.timeout' => 15]);
                // A fragment is not transmitted in server requests/access logs.
                $url = route('invitations.show', $invite->id).'#token='.$token;
                Mail::mailer('smtp')->to($invite->normalized_email)->send(new CommunityInvitationMail($community->name, $url));
                $invite->sent_at = now();
                $invite->save();
            });
        } catch (Throwable) {
            // Do not chain SMTP exceptions: failed_jobs/logs must not contain tokens.
            throw new RuntimeException('Invitation delivery failed for record '.$this->invitationId.'. Retry the delivery job.');
        }
    }
}
