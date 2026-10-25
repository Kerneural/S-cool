<?php

use App\Http\Controllers\Admin\CommunityController as AdminCommunityController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityCoverController;
use App\Http\Controllers\CommunityInvitationController;
use App\Http\Controllers\CommunityJoinController;
use App\Http\Controllers\CommunityLeaderboardController;
use App\Http\Controllers\CommunityMemberController;
use App\Http\Controllers\CommunityMessageController;
use App\Http\Controllers\CommunityOnboardingController;
use App\Http\Controllers\CommunityOrderController;
use App\Http\Controllers\ContentReportController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseCoverController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\CreatorOverviewController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HelpfulAnswerController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonProgressController;
use App\Http\Controllers\MembershipAccessController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\SePayWebhookController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProofOfWorkController;
use App\Http\Controllers\ProofOfWorkReviewController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SecureVideoStreamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/help', [HelpController::class, 'index'])->name('help.index');

Route::get('/dashboard', [CommunityController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// SePay Sandbox IPN Webhook
Route::post('/webhooks/sepay', [SePayWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.sepay');

Route::get('/invitations/{invitation}', [CommunityInvitationController::class, 'show'])->whereNumber('invitation')->name('invitations.show');
Route::post('/invitations/{invitation}/accept', [CommunityInvitationController::class, 'accept'])
    ->whereNumber('invitation')->middleware(['auth', 'verified', 'throttle:10,1'])->name('invitations.accept');
Route::post('/invitations/{invitation}/checkout', [PaymentController::class, 'initiate'])
    ->whereNumber('invitation')->middleware(['auth', 'verified', 'throttle:10,1'])->name('invitations.checkout');

Route::post('/communities/{community:slug}/join', [CommunityJoinController::class, 'join'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('communities.join');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');
    Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/account/memberships', [MembershipAccessController::class, 'index'])->middleware('verified')->name('account.memberships.index');
    Route::get('/account/memberships/{membership}', [MembershipAccessController::class, 'show'])->middleware('verified')->name('account.memberships.show');
    Route::post('/account/memberships/{membership}/renew', [MembershipAccessController::class, 'renew'])->middleware(['verified', 'throttle:10,1'])->name('account.memberships.renew');
    Route::get('/account/payment-history', [PaymentHistoryController::class, 'index'])->name('account.payment-history.index');
    Route::get('/account/payment-history/{payment}', [PaymentHistoryController::class, 'show'])->name('account.payment-history.show');
    Route::get('/account/referrals', [ReferralController::class, 'memberIndex'])->middleware('verified')->name('account.referrals');

    Route::get('/payments/{reference}/checkout', [PaymentController::class, 'checkout'])->middleware('verified')->name('payments.checkout');
    Route::get('/payments/{reference}/status', [PaymentController::class, 'status'])->middleware('verified')->name('payments.status');
    Route::post('/payments/{reference}/cancel', [PaymentController::class, 'cancel'])->middleware(['verified', 'throttle:10,1'])->name('payments.cancel');

    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/create', [CommunityController::class, 'create'])->name('communities.create');
    Route::post('/communities', [CommunityController::class, 'store'])->name('communities.store');
    Route::get('/communities/{community:slug}', [CommunityController::class, 'show'])->name('communities.show');
    Route::get('/communities/{community:slug}/about', [CommunityController::class, 'about'])->name('communities.about');
    Route::get('/communities/{community:slug}/edit', [CommunityController::class, 'edit'])->name('communities.edit');
    Route::put('/communities/{community:slug}', [CommunityController::class, 'update'])->name('communities.update');
    Route::get('/communities/{community:slug}/cover', [CommunityCoverController::class, 'show'])->name('communities.cover.show');
    Route::post('/communities/{community:slug}/cover', [CommunityCoverController::class, 'store'])->name('communities.cover.store');
    Route::scopeBindings()->group(function () {
        Route::get('/communities/{community:slug}/manage', [CreatorOverviewController::class, 'index'])->name('communities.manage');
        Route::get('/communities/{community:slug}/orders', [CommunityOrderController::class, 'index'])->name('communities.orders.index');
        Route::get('/communities/{community:slug}/referrals', [ReferralController::class, 'creatorIndex'])->name('communities.referrals.index');
        Route::post('/communities/{community:slug}/referrals/request', [ReferralController::class, 'requestInvitation'])->middleware(['verified', 'throttle:10,1'])->name('communities.referrals.request');
        Route::post('/communities/{community:slug}/referrals/{referralRequest}/approve', [ReferralController::class, 'approveRequest'])->name('communities.referrals.approve');
        Route::post('/communities/{community:slug}/referrals/{referralRequest}/reject', [ReferralController::class, 'rejectRequest'])->name('communities.referrals.reject');
        Route::get('/communities/{community:slug}/reports', [ContentReportController::class, 'index'])->name('communities.reports.index');
        Route::post('/communities/{community:slug}/reports', [ContentReportController::class, 'store'])->middleware(['auth', 'verified', 'throttle:20,1'])->name('communities.reports.store');
        Route::post('/communities/{community:slug}/reports/{report}/remove', [ContentReportController::class, 'remove'])->name('communities.reports.remove');
        Route::post('/communities/{community:slug}/reports/{report}/dismiss', [ContentReportController::class, 'dismiss'])->name('communities.reports.dismiss');
        Route::get('/communities/{community:slug}/invitations', [CommunityInvitationController::class, 'index'])->name('communities.invitations.index');
        Route::post('/communities/{community:slug}/invitations', [CommunityInvitationController::class, 'store'])->middleware('throttle:10,1')->name('communities.invitations.store');
        Route::delete('/communities/{community:slug}/invitations/{invitation}', [CommunityInvitationController::class, 'revoke'])->name('communities.invitations.revoke');

        Route::get('/communities/{community:slug}/members', [CommunityMemberController::class, 'index'])->name('communities.members.index');
        Route::post('/communities/{community:slug}/members/{membership}/suspend', [CommunityMemberController::class, 'suspend'])->name('communities.members.suspend');
        Route::post('/communities/{community:slug}/members/{membership}/reactivate', [CommunityMemberController::class, 'reactivate'])->name('communities.members.reactivate');
        Route::post('/communities/{community:slug}/members/{membership}/remove', [CommunityMemberController::class, 'remove'])->name('communities.members.remove');

        Route::get('/communities/{community:slug}/directory', [DirectoryController::class, 'index'])->name('communities.directory.index');
        Route::get('/communities/{community:slug}/directory/{user}', [DirectoryController::class, 'show'])->withoutScopedBindings()->name('communities.directory.show');
        Route::get('/communities/{community:slug}/directory/{user}/avatar', [DirectoryController::class, 'avatar'])->withoutScopedBindings()->name('communities.directory.avatar');

        Route::get('/communities/{community:slug}/posts', [PostController::class, 'index'])->name('communities.posts.index');
        Route::get('/communities/{community:slug}/posts/create', [PostController::class, 'create'])->name('communities.posts.create');
        Route::post('/communities/{community:slug}/posts', [PostController::class, 'store'])->name('communities.posts.store');
        Route::get('/communities/{community:slug}/posts/{post}', [PostController::class, 'show'])->name('communities.posts.show');
        Route::get('/communities/{community:slug}/posts/{post}/edit', [PostController::class, 'edit'])->name('communities.posts.edit');
        Route::put('/communities/{community:slug}/posts/{post}', [PostController::class, 'update'])->name('communities.posts.update');
        Route::delete('/communities/{community:slug}/posts/{post}', [PostController::class, 'destroy'])->name('communities.posts.destroy');
        Route::post('/communities/{community:slug}/posts/{post}/pin', [PostController::class, 'pin'])->name('communities.posts.pin');
        Route::post('/communities/{community:slug}/posts/{post}/unpin', [PostController::class, 'unpin'])->name('communities.posts.unpin');
        Route::post('/communities/{community:slug}/posts/{post}/like', [PostReactionController::class, 'like'])->name('communities.posts.like');
        Route::delete('/communities/{community:slug}/posts/{post}/like', [PostReactionController::class, 'unlike'])->name('communities.posts.unlike');
        Route::post('/communities/{community:slug}/posts/{post}/save', [PostReactionController::class, 'save'])->name('communities.posts.save');
        Route::delete('/communities/{community:slug}/posts/{post}/save', [PostReactionController::class, 'unsave'])->name('communities.posts.unsave');
        Route::get('/communities/{community:slug}/saved', [PostReactionController::class, 'saved'])->name('communities.saved.index');
        Route::get('/communities/{community:slug}/leaderboard', [CommunityLeaderboardController::class, 'index'])->name('communities.leaderboard');
        Route::post('/communities/{community:slug}/onboarding/video', [CommunityOnboardingController::class, 'acknowledgeVideo'])->name('communities.onboarding.video');
        Route::get('/communities/{community:slug}/search', [SearchController::class, 'index'])->name('communities.search');
        Route::get('/communities/{community:slug}/notifications', [NotificationController::class, 'index'])->name('communities.notifications.index');
        Route::patch('/communities/{community:slug}/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('communities.notifications.read');
        Route::post('/communities/{community:slug}/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('communities.notifications.read-all');
        Route::get('/communities/{community:slug}/notifications/{notification}/go', [NotificationController::class, 'follow'])->name('communities.notifications.follow');

        Route::get('/communities/{community:slug}/messages', [CommunityMessageController::class, 'index'])->name('communities.messages.index');
        Route::post('/communities/{community:slug}/messages/start', [CommunityMessageController::class, 'start'])->name('communities.messages.start');
        Route::get('/communities/{community:slug}/messages/{conversation}', [CommunityMessageController::class, 'show'])->name('communities.messages.show');
        Route::post('/communities/{community:slug}/messages/{conversation}', [CommunityMessageController::class, 'store'])->middleware('throttle:30,1')->name('communities.messages.store');

        Route::post('/communities/{community:slug}/categories', [PostCategoryController::class, 'store'])->name('communities.categories.store');
        Route::delete('/communities/{community:slug}/categories/{category}', [PostCategoryController::class, 'destroy'])->name('communities.categories.destroy');

        Route::post('/communities/{community:slug}/posts/{post}/comments', [CommentController::class, 'store'])->name('communities.posts.comments.store');
        Route::get('/communities/{community:slug}/posts/{post}/comments/{comment}/edit', [CommentController::class, 'edit'])->name('communities.posts.comments.edit');
        Route::put('/communities/{community:slug}/posts/{post}/comments/{comment}', [CommentController::class, 'update'])->name('communities.posts.comments.update');
        Route::delete('/communities/{community:slug}/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])->name('communities.posts.comments.destroy');
        Route::post('/communities/{community:slug}/posts/{post}/comments/{comment}/helpful-answer', [HelpfulAnswerController::class, 'store'])->name('communities.posts.comments.helpful-answer.store');
        Route::delete('/communities/{community:slug}/posts/{post}/comments/{comment}/helpful-answer', [HelpfulAnswerController::class, 'destroy'])->name('communities.posts.comments.helpful-answer.destroy');

        Route::get('/communities/{community:slug}/events', [EventController::class, 'index'])->name('communities.events.index');
        Route::get('/communities/{community:slug}/events/create', [EventController::class, 'create'])->name('communities.events.create');
        Route::post('/communities/{community:slug}/events', [EventController::class, 'store'])->name('communities.events.store');
        Route::get('/communities/{community:slug}/events/{event}', [EventController::class, 'show'])->name('communities.events.show');
        Route::get('/communities/{community:slug}/events/{event}/edit', [EventController::class, 'edit'])->name('communities.events.edit');
        Route::put('/communities/{community:slug}/events/{event}', [EventController::class, 'update'])->name('communities.events.update');
        Route::post('/communities/{community:slug}/events/{event}/cancel', [EventController::class, 'cancel'])->name('communities.events.cancel');

        // Classroom, Courses, Sections, and Lessons
        Route::get('/communities/{community:slug}/classroom', [ClassroomController::class, 'index'])->name('communities.classroom.index');
        Route::post('/communities/{community:slug}/courses', [CourseController::class, 'store'])->name('communities.courses.store');
        Route::post('/communities/{community:slug}/courses/reorder', [CourseController::class, 'reorder'])->name('communities.courses.reorder');
        Route::get('/communities/{community:slug}/courses/{course}', [CourseController::class, 'show'])->name('communities.courses.show');
        Route::get('/communities/{community:slug}/courses/{course}/cover', [CourseCoverController::class, 'show'])->name('communities.courses.cover');
        Route::post('/communities/{community:slug}/courses/{course}/cover', [CourseCoverController::class, 'store'])->name('communities.courses.cover.store');
        Route::delete('/communities/{community:slug}/courses/{course}/cover', [CourseCoverController::class, 'destroy'])->name('communities.courses.cover.destroy');
        Route::post('/communities/{community:slug}/courses/{course}/checkout', [PaymentController::class, 'initiateCourse'])->middleware('verified')->name('communities.courses.checkout');
        Route::put('/communities/{community:slug}/courses/{course}', [CourseController::class, 'update'])->name('communities.courses.update');
        Route::delete('/communities/{community:slug}/courses/{course}', [CourseController::class, 'destroy'])->name('communities.courses.destroy');

        Route::post('/communities/{community:slug}/courses/{course}/sections', [CourseSectionController::class, 'store'])->name('communities.courses.sections.store');
        Route::put('/communities/{community:slug}/courses/{course}/sections/{section}', [CourseSectionController::class, 'update'])->name('communities.courses.sections.update');
        Route::delete('/communities/{community:slug}/courses/{course}/sections/{section}', [CourseSectionController::class, 'destroy'])->name('communities.courses.sections.destroy');
        Route::post('/communities/{community:slug}/courses/{course}/sections/reorder', [CourseSectionController::class, 'reorder'])->name('communities.courses.sections.reorder');

        Route::get('/communities/{community:slug}/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->name('communities.lessons.show');
        Route::post('/communities/{community:slug}/courses/{course}/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])->name('communities.lessons.progress.update');
        Route::post('/communities/{community:slug}/courses/{course}/lessons/{lesson}/proof-of-work', [ProofOfWorkController::class, 'store'])->name('communities.lessons.proof-of-work.store');
        Route::get('/communities/{community:slug}/proof-of-work/{proofOfWork}/image', [ProofOfWorkController::class, 'image'])->name('communities.proof-of-work.image');

        // Creator Proof of Work Review Queue
        Route::get('/communities/{community:slug}/manage/proof-of-work', [ProofOfWorkReviewController::class, 'index'])->name('communities.manage.proof-of-work.index');
        Route::get('/communities/{community:slug}/manage/proof-of-work/{proofOfWork}', [ProofOfWorkReviewController::class, 'show'])->name('communities.manage.proof-of-work.show');
        Route::post('/communities/{community:slug}/manage/proof-of-work/{proofOfWork}/review', [ProofOfWorkReviewController::class, 'review'])->name('communities.manage.proof-of-work.review');

        Route::post('/communities/{community:slug}/courses/{course}/sections/{section}/lessons', [LessonController::class, 'store'])->name('communities.lessons.store');
        Route::put('/communities/{community:slug}/courses/{course}/sections/{section}/lessons/{lesson}', [LessonController::class, 'update'])->name('communities.lessons.update');
        Route::delete('/communities/{community:slug}/courses/{course}/sections/{section}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('communities.lessons.destroy');
        Route::post('/communities/{community:slug}/courses/{course}/sections/{section}/lessons/reorder', [LessonController::class, 'reorder'])->name('communities.lessons.reorder');
    });
});

Route::middleware(['auth', 'verified', 'platform-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/communities', [AdminCommunityController::class, 'index'])->name('communities.index');
    Route::post('/communities/{community}/suspend', [AdminCommunityController::class, 'suspend'])->name('communities.suspend');
    Route::post('/communities/{community}/reactivate', [AdminCommunityController::class, 'reactivate'])->name('communities.reactivate');
});

Route::get('/secure-stream/video', [SecureVideoStreamController::class, 'stream'])->name('secure-stream.video');

require __DIR__.'/auth.php';
