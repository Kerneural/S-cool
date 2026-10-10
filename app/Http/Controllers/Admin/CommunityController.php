<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\PlatformAdminAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * Display a minimal listing of all communities for Platform Administration.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $query = Community::query()->with(['creator', 'platformAdminActions.admin']);

        if (in_array($statusFilter, ['ACTIVE', 'SUSPENDED', 'ARCHIVED'], true)) {
            $query->where('status', $statusFilter);
        }

        $communities = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $stats = [
            'total' => Community::count(),
            'active' => Community::where('status', 'ACTIVE')->count(),
            'suspended' => Community::where('status', 'SUSPENDED')->count(),
            'archived' => Community::where('status', 'ARCHIVED')->count(),
        ];

        return view('admin.communities.index', compact('communities', 'statusFilter', 'stats'));
    }

    /**
     * Suspend an active community with an audit reason.
     */
    public function suspend(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $admin = $request->user();

        DB::transaction(function () use ($community, $admin, $validated) {
            /** @var Community $lockedCommunity */
            $lockedCommunity = Community::lockForUpdate()->findOrFail($community->id);

            if ($lockedCommunity->status === 'ARCHIVED') {
                throw ValidationException::withMessages([
                    'community' => __('Archived communities cannot be suspended.'),
                ]);
            }

            if ($lockedCommunity->status === 'SUSPENDED') {
                throw ValidationException::withMessages([
                    'community' => __('This community is already suspended.'),
                ]);
            }

            $lockedCommunity->forceFill(['status' => 'SUSPENDED'])->save();

            PlatformAdminAction::create([
                'admin_id' => $admin->id,
                'community_id' => $lockedCommunity->id,
                'action' => 'SUSPEND',
                'reason' => $validated['reason'],
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.communities.index')->with('status', __('Community has been suspended.'));
    }

    /**
     * Reactivate a suspended community with an audit reason.
     */
    public function reactivate(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $admin = $request->user();

        DB::transaction(function () use ($community, $admin, $validated) {
            /** @var Community $lockedCommunity */
            $lockedCommunity = Community::lockForUpdate()->findOrFail($community->id);

            if ($lockedCommunity->status === 'ARCHIVED') {
                throw ValidationException::withMessages([
                    'community' => __('Archived communities cannot be reactivated.'),
                ]);
            }

            if ($lockedCommunity->status === 'ACTIVE') {
                throw ValidationException::withMessages([
                    'community' => __('This community is already active.'),
                ]);
            }

            $lockedCommunity->forceFill(['status' => 'ACTIVE'])->save();

            PlatformAdminAction::create([
                'admin_id' => $admin->id,
                'community_id' => $lockedCommunity->id,
                'action' => 'REACTIVATE',
                'reason' => $validated['reason'],
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.communities.index')->with('status', __('Community has been reactivated.'));
    }
}
