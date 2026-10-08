<?php

use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityCoverController;
use App\Http\Controllers\CommunityInvitationController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\LessonController;
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

        // Classroom, Courses, Sections, and Lessons
        Route::get('/communities/{community:slug}/classroom', [ClassroomController::class, 'index'])->name('communities.classroom.index');
        Route::post('/communities/{community:slug}/courses', [CourseController::class, 'store'])->name('communities.courses.store');
        Route::post('/communities/{community:slug}/courses/reorder', [CourseController::class, 'reorder'])->name('communities.courses.reorder');
        Route::get('/communities/{community:slug}/courses/{course}', [CourseController::class, 'show'])->name('communities.courses.show');
        Route::put('/communities/{community:slug}/courses/{course}', [CourseController::class, 'update'])->name('communities.courses.update');
        Route::delete('/communities/{community:slug}/courses/{course}', [CourseController::class, 'destroy'])->name('communities.courses.destroy');

        Route::post('/communities/{community:slug}/courses/{course}/sections', [CourseSectionController::class, 'store'])->name('communities.courses.sections.store');
        Route::put('/communities/{community:slug}/courses/{course}/sections/{section}', [CourseSectionController::class, 'update'])->name('communities.courses.sections.update');
        Route::delete('/communities/{community:slug}/courses/{course}/sections/{section}', [CourseSectionController::class, 'destroy'])->name('communities.courses.sections.destroy');
        Route::post('/communities/{community:slug}/courses/{course}/sections/reorder', [CourseSectionController::class, 'reorder'])->name('communities.courses.sections.reorder');

        Route::get('/communities/{community:slug}/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->name('communities.lessons.show');
        Route::post('/communities/{community:slug}/courses/{course}/sections/{section}/lessons', [LessonController::class, 'store'])->name('communities.lessons.store');
        Route::put('/communities/{community:slug}/courses/{course}/sections/{section}/lessons/{lesson}', [LessonController::class, 'update'])->name('communities.lessons.update');
        Route::delete('/communities/{community:slug}/courses/{course}/sections/{section}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('communities.lessons.destroy');
        Route::post('/communities/{community:slug}/courses/{course}/sections/{section}/lessons/reorder', [LessonController::class, 'reorder'])->name('communities.lessons.reorder');
    });
});

require __DIR__.'/auth.php';
