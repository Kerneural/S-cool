<?php

namespace App\View\Components;

use App\Models\Community;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;
use Illuminate\View\View;

class SkoolCommunityCard extends Component
{
    public Community $community;

    public int $memberCount;

    public string $formattedMemberCount;

    public int $onlineCount;

    public string $formattedOnlineCount;

    public int $adminCount;

    public Collection $sampleMembers;

    public bool $isMember;

    public bool $isCreator;

    public function __construct(Community $community)
    {
        $this->community = $community;
        $user = Auth::user();

        // 1. Real member count: non-mocked effective members from DB
        $this->memberCount = $community->memberships()->effective()->count();
        $this->formattedMemberCount = self::formatCount($this->memberCount);

        // 2. Dynamic online count: active sessions within the last 15 minutes
        $this->onlineCount = $this->calculateOnlineCount();
        $this->formattedOnlineCount = self::formatCount($this->onlineCount);

        // 3. Admin count: creator is the administrator (1)
        $this->adminCount = 1;

        // 4. Sample member avatars (up to 8)
        $sample = $community->memberships()
            ->effective()
            ->with('user')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->pluck('user')
            ->filter();

        if ($sample->count() < 8 && $community->creator) {
            if (! $sample->contains('id', $community->creator_id)) {
                $sample->prepend($community->creator);
            }
        }

        $this->sampleMembers = $sample->values()->take(8);

        // 5. Membership state for current viewer
        $this->isCreator = $community->isCreator($user);
        $this->isMember = $community->isActiveMember($user);
    }

    protected function calculateOnlineCount(): int
    {
        $memberUserIds = $this->community->memberships()
            ->effective()
            ->pluck('user_id')
            ->push($this->community->creator_id)
            ->unique();

        $count = 0;
        if (Schema::hasTable('sessions')) {
            $count = DB::table('sessions')
                ->whereIn('user_id', $memberUserIds)
                ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                ->distinct('user_id')
                ->count();
        }

        if (Auth::check() && $memberUserIds->contains(Auth::id())) {
            $count = max(1, $count);
        }

        return $count;
    }

    public static function formatCount(int $count): string
    {
        if ($count >= 1000000) {
            $formatted = round($count / 1000000, 1);

            return (floor($formatted) == $formatted ? (int) $formatted : $formatted).'M';
        }

        if ($count >= 1000) {
            $formatted = round($count / 1000, 1);

            return (floor($formatted) == $formatted ? (int) $formatted : $formatted).'k';
        }

        return (string) $count;
    }

    public function render(): View
    {
        return view('components.skool-community-card');
    }
}
