<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityOrderController extends Controller
{
    /**
     * Display a paginated listing of community orders / payment attempts for the owning creator.
     */
    public function index(Request $request, Community $community): View
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);

        $allowedStatuses = [
            Payment::STATUS_SUCCEEDED,
            Payment::STATUS_PENDING,
            Payment::STATUS_CANCELLED,
            Payment::STATUS_FAILED,
        ];

        // Safe status input parsing: reject arrays and non-string inputs
        $rawStatus = $request->input('status');
        $statusFilter = is_string($rawStatus) ? strtoupper(trim($rawStatus)) : 'ALL';
        if (! in_array($statusFilter, array_merge(['ALL'], $allowedStatuses), true)) {
            $statusFilter = 'ALL';
        }

        // Date range filter parsing & validation in UTC (range days or custom from/to)
        $rawRange = $request->input('range');
        $rawFrom = $request->input('from');
        $rawTo = $request->input('to');

        $rangeFilter = 'ALL';
        $startDate = null;
        $endDateExclusive = null;

        if (is_string($rawFrom) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawFrom)
            && is_string($rawTo) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTo)) {
            try {
                $start = Carbon::createFromFormat('Y-m-d', $rawFrom, 'UTC')->startOfDay();
                $end = Carbon::createFromFormat('Y-m-d', $rawTo, 'UTC')->startOfDay()->addDay();
                if ($start->lte($end)) {
                    $startDate = $start;
                    $endDateExclusive = $end;
                    $rangeFilter = 'custom';
                }
            } catch (\Exception) {
                // Ignore invalid date strings
            }
        } elseif (is_numeric($rawRange)) {
            $rangeDays = max(1, min(90, (int) $rawRange));
            $rangeFilter = (string) $rangeDays;
            $startDate = now('UTC')->subDays($rangeDays - 1)->startOfDay();
            $endDateExclusive = now('UTC')->startOfDay()->addDay();
        }

        // Summary counters across all attempts for this tenant
        $summary = [
            'total' => $community->payments()->count(),
            'succeeded' => $community->payments()->where('status', Payment::STATUS_SUCCEEDED)->count(),
            'succeeded_sum' => (int) $community->payments()->where('status', Payment::STATUS_SUCCEEDED)->sum('amount'),
            'pending' => $community->payments()->where('status', Payment::STATUS_PENDING)->count(),
            'cancelled' => $community->payments()->where('status', Payment::STATUS_CANCELLED)->count(),
            'failed' => $community->payments()->where('status', Payment::STATUS_FAILED)->count(),
        ];

        $ordersQuery = $community->payments()
            ->with(['user', 'invitation', 'course'])
            ->orderByDesc('created_at');

        if ($statusFilter !== 'ALL') {
            $ordersQuery->where('status', $statusFilter);
        }

        if ($startDate && $endDateExclusive) {
            $ordersQuery->whereRaw('created_at >= ? AND created_at < ?', [$startDate, $endDateExclusive]);
        }

        $orders = $ordersQuery->paginate(15)->withQueryString();

        return view('communities.orders.index', [
            'community' => $community,
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'rangeFilter' => $rangeFilter,
            'rawFrom' => is_string($rawFrom) ? $rawFrom : null,
            'rawTo' => is_string($rawTo) ? $rawTo : null,
            'summary' => $summary,
        ]);
    }
}
