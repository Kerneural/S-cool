<?php

namespace App\Support;

use App\Services\VideoEmbedService;
use Closure;
use Illuminate\Validation\Rule;

class SocialContent
{
    public static function safeUrl(mixed $value): bool
    {
        if (! is_string($value) || strlen($value) > 2048 || preg_match('/[\x00-\x20\x7f<>"\']/', $value)) {
            return false;
        }
        $parts = parse_url($value);

        return is_array($parts) && ($parts['scheme'] ?? '') === 'https'
            && ! empty($parts['host']) && ! isset($parts['user']) && ! isset($parts['pass'])
            && (! isset($parts['port']) || $parts['port'] === 443)
            && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public static function rules(): array
    {
        return [
            'content_format' => ['sometimes', Rule::in(['plain', 'markdown'])],
            'link_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void {
                if (! self::safeUrl($value)) {
                    $fail(__('Enter a valid HTTPS link.'));
                }
            }],
            'link_title' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, Closure $fail): void {
                if (! VideoEmbedService::isValid($value)) {
                    $fail(__('Use a YouTube, Vimeo or Loom HTTPS video link.'));
                }
            }],
        ];
    }

    public static function firstUrl(string $body, string $format = 'plain'): ?string
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.self::html($body, $format), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach ($dom->getElementsByTagName('a') as $link) {
            $url = $link->getAttribute('href');
            if (self::safeUrl($url)) {
                return $url;
            }
        }

        return null;
    }

    public static function html(?string $body, ?string $format = 'plain'): string
    {
        if ($format === 'markdown') {
            // Course-independent rendering turns all unmanaged images into links.
            return LessonMarkdown::toHtml($body);
        }

        $parts = preg_split('~(https://[^\s<>"]+)~u', $body ?? '', -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';
        foreach ($parts ?: [] as $index => $part) {
            $html .= $index % 2 === 1 && self::safeUrl($part)
                ? '<a href="'.e($part).'" target="_blank" rel="nofollow noopener noreferrer">'.e($part).'</a>'
                : e($part);
        }

        return nl2br($html, false);
    }
}
