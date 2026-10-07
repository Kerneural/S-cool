<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityCoverController;
use App\Http\Controllers\CommunityInvitationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [CommunityController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/invitations/{invitation}', [CommunityInvitationController::class, 'show'])->whereNumber('invitation')->name('invitations.show');
Route::post('/invitations/{invitation}/accept', [CommunityInvitationController::class, 'accept'])
    ->whereNumber('invitation')->middleware(['auth', 'verified', 'throttle:10,1'])->name('invitations.accept');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/create', [CommunityController::class, 'create'])->name('communities.create');
    Route::post('/communities', [CommunityController::class, 'store'])->name('communities.store');
    Route::get('/communities/{community:slug}', [CommunityController::class, 'show'])->name('communities.show');
    Route::get('/communities/{community:slug}/edit', [CommunityController::class, 'edit'])->name('communities.edit');
    Route::put('/communities/{community:slug}', [CommunityController::class, 'update'])->name('communities.update');
    Route::get('/communities/{community:slug}/cover', [CommunityCoverController::class, 'show'])->name('communities.cover.show');
    Route::post('/communities/{community:slug}/cover', [CommunityCoverController::class, 'store'])->name('communities.cover.store');
    Route::scopeBindings()->group(function () {
        Route::get('/communities/{community:slug}/invitations', [CommunityInvitationController::class, 'index'])->name('communities.invitations.index');
        Route::post('/communities/{community:slug}/invitations', [CommunityInvitationController::class, 'store'])->middleware('throttle:10,1')->name('communities.invitations.store');
        Route::delete('/communities/{community:slug}/invitations/{invitation}', [CommunityInvitationController::class, 'revoke'])->name('communities.invitations.revoke');

        Route::get('/communities/{community:slug}/posts', [PostController::class, 'index'])->name('communities.posts.index');
        Route::post('/communities/{community:slug}/posts', [PostController::class, 'store'])->name('communities.posts.store');
        Route::get('/communities/{community:slug}/posts/{post}', [PostController::class, 'show'])->name('communities.posts.show');
        Route::get('/communities/{community:slug}/posts/{post}/edit', [PostController::class, 'edit'])->name('communities.posts.edit');
        Route::put('/communities/{community:slug}/posts/{post}', [PostController::class, 'update'])->name('communities.posts.update');
        Route::delete('/communities/{community:slug}/posts/{post}', [PostController::class, 'destroy'])->name('communities.posts.destroy');

        Route::post('/communities/{community:slug}/posts/{post}/comments', [CommentController::class, 'store'])->name('communities.posts.comments.store');
        Route::delete('/communities/{community:slug}/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])->name('communities.posts.comments.destroy');
    });
});

require __DIR__.'/auth.php';
