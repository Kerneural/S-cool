<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CommunityCoverController extends Controller
{
    public function store(Request $request, Community $community): RedirectResponse
    {
        Gate::authorize('update', $community);
        $request->validate(['cover' => [
            'required',
            File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
            File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(2048),
        ]]);
        $disk = Storage::disk('community_media');
        $newPath = null;
        $oldPath = null;
        try {
            DB::transaction(function () use ($request, $community, $disk, &$newPath, &$oldPath): void {
                $current = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
                Gate::forUser($request->user())->authorize('update', $current);
                $oldPath = $current->cover_path;
                $newPath = $disk->putFile((string) $current->id, $request->file('cover'));
                if (! is_string($newPath)) {
                    throw new \RuntimeException('Cover storage failed.');
                }
                $current->cover_path = $newPath;
                $current->save();
            });
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                $this->cleanup($newPath, $community->id);
            }
            throw $exception;
        }
        if (is_string($oldPath)) {
            $this->cleanup($oldPath, $community->id);
        }

        return redirect()->route('communities.edit', $community)->with('status', 'Cover updated.');
    }

    public function show(Community $community): StreamedResponse
    {
        Gate::authorize('view', $community);
        $path = $community->cover_path;
        abort_unless(is_string($path) && preg_match('/\A'.preg_quote((string) $community->id, '/').'\/[a-zA-Z0-9]{40}\.(?:jpg|jpeg|png|webp)\z/D', $path) === 1, 404);
        $disk = Storage::disk('community_media');
        abort_unless($disk->exists($path), 404);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $type = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/'.$extension;

        return $disk->response($path, 'cover.'.$extension, [
            'Content-Type' => $type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    private function cleanup(string $path, int $communityId): void
    {
        if (preg_match('/\A'.preg_quote((string) $communityId, '/').'\/[a-zA-Z0-9]{40}\.(?:jpg|jpeg|png|webp)\z/D', $path) !== 1) {
            Log::warning('Community cover cleanup rejected an invalid reference.', ['community_id' => $communityId]);

            return;
        }
        try {
            if (! Storage::disk('community_media')->delete($path)) {
                Log::warning('Community cover cleanup failed.', ['community_id' => $communityId]);
            }
        } catch (Throwable) {
            Log::warning('Community cover cleanup failed.', ['community_id' => $communityId]);
        }
    }
}
