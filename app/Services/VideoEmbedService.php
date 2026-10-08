<?php

namespace App\Services;

class VideoEmbedService
{
    /**
     * Parse and validate a video URL.
     * Returns an array with ['provider' => 'youtube'|'vimeo', 'video_id' => '...', 'embed_url' => '...']
     * or null if invalid.
     *
     * @return array{provider: string, video_id: string, embed_url: string}|null
     */
    public static function parse(?string $url): ?array
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // Reject raw html/iframe or script characters
        if (str_contains($url, '<') || str_contains($url, '>') || str_contains($url, '"') || str_contains($url, "'")) {
            return null;
        }

        $parsed = parse_url($url);
        if (! is_array($parsed)) {
            return null;
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        if ($scheme !== 'https') {
            return null;
        }

        // Reject credential-bearing URLs (user or pass)
        if (array_key_exists('user', $parsed) || array_key_exists('pass', $parsed)
            || (isset($parsed['port']) && $parsed['port'] !== 443)
            || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return null;
        }

        $host = strtolower($parsed['host'] ?? '');
        $path = $parsed['path'] ?? '';
        $query = $parsed['query'] ?? '';

        // Strict host checking - exact match only, no lookalike domains
        if ($host === 'youtube.com' || $host === 'www.youtube.com' || $host === 'm.youtube.com') {
            $videoId = null;
            if ($path === '/watch') {
                parse_str($query, $queryParams);
                $videoId = $queryParams['v'] ?? null;
            } elseif (preg_match('#^/(?:embed|v)/([a-zA-Z0-9_-]{11})$#', $path, $matches)) {
                $videoId = $matches[1];
            }

            if ($videoId && is_string($videoId) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                return [
                    'provider' => 'youtube',
                    'video_id' => $videoId,
                    'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                ];
            }

            return null;
        }

        if ($host === 'youtu.be') {
            if (preg_match('#^/([a-zA-Z0-9_-]{11})$#', $path, $matches)) {
                $videoId = $matches[1];

                return [
                    'provider' => 'youtube',
                    'video_id' => $videoId,
                    'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                ];
            }

            return null;
        }

        if ($host === 'vimeo.com' || $host === 'www.vimeo.com') {
            if (preg_match('#^/([0-9]{5,15})$#', $path, $matches)) {
                $videoId = $matches[1];

                return [
                    'provider' => 'vimeo',
                    'video_id' => $videoId,
                    'embed_url' => "https://player.vimeo.com/video/{$videoId}",
                ];
            }

            return null;
        }

        if ($host === 'player.vimeo.com') {
            if (preg_match('#^/video/([0-9]{5,15})$#', $path, $matches)) {
                $videoId = $matches[1];

                return [
                    'provider' => 'vimeo',
                    'video_id' => $videoId,
                    'embed_url' => "https://player.vimeo.com/video/{$videoId}",
                ];
            }

            return null;
        }

        return null;
    }

    public static function isValid(?string $url): bool
    {
        return self::parse($url) !== null;
    }

    public static function getEmbedUrl(?string $url): ?string
    {
        $res = self::parse($url);

        return $res ? $res['embed_url'] : null;
    }

    public static function getProviderUrl(?string $url): ?string
    {
        $video = self::parse($url);

        if ($video === null) {
            return null;
        }

        // Generate a normal watch page from validated IDs, never echo the input URL.
        return match ($video['provider']) {
            'youtube' => 'https://www.youtube.com/watch?v='.$video['video_id'],
            'vimeo' => 'https://vimeo.com/'.$video['video_id'],
            default => null,
        };
    }
}
