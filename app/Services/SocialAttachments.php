<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\SocialAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class SocialAttachments
{
    public static function rules(): array
    {
        return [
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['required', File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
                File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(5120)],
            'documents' => ['nullable', 'array', 'max:3'],
            'documents.*' => ['required', File::types(['pdf', 'docx', 'xlsx', 'pptx'])->extensions(['pdf', 'docx', 'xlsx', 'pptx'])->max(10240)],
        ];
    }

    /** Stage once outside a retried database transaction; discard on publication failure. */
    public function stage(array $images, array $documents): array
    {
        Validator::make(compact('images', 'documents'), self::rules())->validate();
        $files = [...$images, ...$documents];
        if (array_sum(array_map(fn (UploadedFile $file) => $file->getSize(), $files)) > 20 * 1024 * 1024) {
            throw ValidationException::withMessages(['images' => __('Attachments must total at most 20 MB.')]);
        }
        $staged = [];
        try {
            foreach ($files as $file) {
                $isImage = in_array($file, $images, true);
                $path = Storage::disk('community_media')->putFile('social', $file);
                $staged[] = [
                    'path' => $path,
                    'name' => mb_substr(str_replace(['/', chr(92)], '_', preg_replace('/[\x00-\x1f\x7f]/u', '_', $file->getClientOriginalName())), 0, 200),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(), 'is_image' => $isImage,
                ];
            }
        } catch (\Throwable $exception) {
            $this->discard($staged);
            throw $exception;
        }

        return $staged;
    }

    public function publish(array $staged, Post $post, User $user, ?Comment $comment = null): void
    {
        $existing = $comment ? $comment->attachments : $post->attachments;
        $combined = [...$existing->map(fn ($item) => $item->toArray())->all(), ...$staged];
        if (count(array_filter($combined, fn ($file) => $file['is_image'])) > 4
            || count(array_filter($combined, fn ($file) => ! $file['is_image'])) > 3
            || array_sum(array_column($combined, 'size')) > 20 * 1024 * 1024) {
            throw ValidationException::withMessages(['images' => __('Use at most four images, three files and 20 MB total.')]);
        }
        foreach ($staged as $file) {
            SocialAttachment::create($file + [
                'community_id' => $post->community_id, 'post_id' => $post->id,
                'comment_id' => $comment?->id, 'user_id' => $user->id,
            ]);
        }
    }

    public function discard(array $staged): void
    {
        foreach ($staged as $file) {
            Storage::disk('community_media')->delete($file['path']);
        }
    }
}
