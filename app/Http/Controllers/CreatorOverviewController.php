<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CreatorOverviewController extends Controller
{
    /**
     * Display the creator management overview and truthful statistics.
     */
    public function index(Request $request, Community $community): View
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);

        // Date range clamping: 1 to 90 days, default 30
        $range = max(1, min(90, (int) $request->input('range', 30)));
        $nowUtc = Carbon::now('UTC');
        $startUtc = $nowUtc->copy()->subDays($range - 1)->startOfDay();
        $endUtcExclusive = $nowUtc->copy()->startOfDay()->addDay();

        // 1. Members: Active memberships count; creator shown separately
        $activeMembersCount = $community->memberships()->effective()->count();
        $creator = $community->creator;
        $creatorHasActiveMembership = $community->memberships()
            ->where('user_id', $community->creator_id)
            ->effective()
            ->exists();

        // 2. Content: Non-deleted posts
        $postsCount = $community->posts()->count();

        // 3. Courses: Published courses
        $publishedCoursesCount = $community->courses()->where('status', 'PUBLISHED')->count();

        // 4. Test Mode Receipts: Succeeded payments within the selected date range
        $simulatedReceipts = (int) $community->payments()
            ->where('status', Payment::STATUS_SUCCEEDED)
            ->whereRaw('COALESCE(paid_at, created_at) >= ? AND COALESCE(paid_at, created_at) < ?', [$startUtc, $endUtcExclusive])
            ->sum('amount');

        // 5. Learning Completion Ratio:
        // Formula: completed published lessons divided by (ACTIVE members * published lessons in Published courses)
        // Exclude creator unless creator has an ACTIVE membership
        $publishedLessonsCount = (int) DB::table('lessons')
            ->join('course_sections', 'lessons.course_section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->where('courses.community_id', $community->id)
            ->where('courses.status', 'PUBLISHED')
            ->where('lessons.status', 'PUBLISHED')
            ->count();

        // Numerator and denominator use exactly the same currently accessible pairs.
        $accessiblePairs = DB::table('lessons')
            ->join('course_sections', 'lessons.course_section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->join('community_memberships', 'community_memberships.community_id', '=', 'courses.community_id')
            ->where('courses.community_id', $community->id)
            ->where('courses.status', 'PUBLISHED')
            ->where('lessons.status', 'PUBLISHED')
            ->where(fn ($memberships) => CommunityMembership::applyEffectiveAccess($memberships))
            ->where(function ($query): void {
                $query->where('courses.access_mode', 'INCLUDED')
                    ->orWhereExists(function ($entitlements): void {
                        $entitlements->selectRaw('1')->from('course_entitlements')
                            ->whereColumn('course_entitlements.community_id', 'courses.community_id')
                            ->whereColumn('course_entitlements.course_id', 'courses.id')
                            ->whereColumn('course_entitlements.user_id', 'community_memberships.user_id');
                    });
            });
        $denominator = (clone $accessiblePairs)->count();
        $accessibleLessonPairsCount = $denominator;
        $completedLessonsCount = (clone $accessiblePairs)
            ->join('lesson_progresses', function ($join): void {
                $join->on('lesson_progresses.lesson_id', '=', 'lessons.id')
                    ->on('lesson_progresses.user_id', '=', 'community_memberships.user_id');
            })->where('lesson_progresses.completed', 1)->count();

        $completionRatio = $denominator > 0 ? round(($completedLessonsCount / $denominator) * 100, 1) : 0;
        $hasCompletionData = $denominator > 0;

        // Activity / payment attempts table within the date range (paginated, with user)
        $recentPayments = $community->payments()
            ->with(['user', 'invitation'])
            ->whereRaw('created_at >= ? AND created_at < ?', [$startUtc, $endUtcExclusive])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('communities.manage', [
            'community' => $community,
            'creator' => $creator,
            'creatorHasActiveMembership' => $creatorHasActiveMembership,
            'activeMembersCount' => $activeMembersCount,
            'postsCount' => $postsCount,
            'publishedCoursesCount' => $publishedCoursesCount,
            'simulatedReceipts' => $simulatedReceipts,
            'formattedSimulatedReceipts' => number_format($simulatedReceipts, 0, ',', '.').' VND',
            'range' => $range,
            'startUtc' => $startUtc,
            'endUtcExclusive' => $endUtcExclusive,
            'publishedLessonsCount' => $publishedLessonsCount,
            'completedLessonsCount' => $completedLessonsCount,
            'accessibleLessonPairsCount' => $accessibleLessonPairsCount,
            'completionRatio' => $completionRatio,
            'hasCompletionData' => $hasCompletionData,
            'recentPayments' => $recentPayments,
        ]);
    }
}
