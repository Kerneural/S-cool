<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $disk = Storage::disk('local');
        $newAvatarPath = null;
        $oldAvatarPath = $user->avatar_path;

        if ($request->hasFile('avatar')) {
            $newAvatarPath = $request->file('avatar')->store('avatars', 'local');
            if (! is_string($newAvatarPath)) {
                throw new \RuntimeException('Avatar storage failed.');
            }
            $validated['avatar_path'] = $newAvatarPath;
        }

        try {
            DB::transaction(function () use ($user, $validated): void {
                $user->fill($validated);

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $user->save();
            });
        } catch (\Throwable $exception) {
            if (is_string($newAvatarPath) && $disk->exists($newAvatarPath)) {
                $disk->delete($newAvatarPath);
            }
            throw $exception;
        }

        if (is_string($newAvatarPath) && is_string($oldAvatarPath) && $disk->exists($oldAvatarPath)) {
            $disk->delete($oldAvatarPath);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update user notification preferences (F-14 / EUR-51).
     * Supports both async JSON requests with server acknowledgment and classic form redirects.
     */
    public function updateNotifications(Request $request): SymfonyResponse
    {
        $fields = ['notify_replies', 'notify_courses'];
        $unknown = array_diff(array_keys($request->except(['_token', '_method'])), $fields);
        if (($request->expectsJson() && $unknown !== []) || ! $request->hasAny($fields)) {
            throw ValidationException::withMessages([
                'preferences' => 'Provide only the supported notification preferences.',
            ]);
        }

        $validated = $request->validate([
            'notify_replies' => ['sometimes', 'boolean'],
            'notify_courses' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        if ($request->has('notify_replies')) {
            $user->notify_replies = $request->boolean('notify_replies');
        }

        if ($request->has('notify_courses')) {
            $user->notify_courses = $request->boolean('notify_courses');
        }

        $user->save();

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'status' => 'success',
                'notify_replies' => (bool) $user->notify_replies,
                'notify_courses' => (bool) $user->notify_courses,
            ]);
        }

        return Redirect::route('profile.edit', ['section' => 'notifications'])->with('status', 'notification-preferences-updated');
    }

    /**
     * Stream or display the authenticated user's avatar.
     */
    public function avatar(Request $request): SymfonyResponse
    {
        $user = $request->user();
        $path = $user?->avatar_path;
        $disk = Storage::disk('local');

        if (is_string($path) && preg_match('/\Aavatars\/[a-zA-Z0-9_\-\.]+\.(?:jpg|jpeg|png|webp)\z/D', $path) === 1 && $disk->exists($path)) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/'.$extension;

            return $disk->response($path, 'avatar.'.$extension, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ], 'inline');
        }

        return response(
            '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>',
            200,
            [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        DB::transaction(function () use ($request): void {
            // Lock the referenced user while checking ownership and deleting it.
            $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

            if ($user->createdCommunities()->exists() || $user->memberships()->exists()
                || $user->platformAdminActions()->exists()) {
                throw ValidationException::withMessages([
                    'password' => 'Accounts with community ownership, membership or platform audit records cannot be deleted. Retention must be resolved first.',
                ])->errorBag('userDeletion');
            }

            // Logout rotates the remember token; do it while the user still exists.
            Auth::logout();
            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
