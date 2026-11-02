<?php

namespace App\Services;

use App\Support\SocialContent;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\IpUtils;

class SocialLinkPreview
{
    /** Only public HTTPS, pinned DNS, no redirects/cookies/proxy or remote image loads. */
    public function fetch(string $url): array
    {
        if (! SocialContent::safeUrl($url) || ! defined('CURLOPT_RESOLVE')) {
            return [];
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (! preg_match('/\A[a-zA-Z0-9](?:[a-zA-Z0-9.-]*[a-zA-Z0-9])?\z/D', $host)) {
            return [];
        }
        $addresses = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? [$host] : (gethostbynamel($host) ?: []);
        if ($addresses === [] || collect($addresses)->contains(fn ($ip) => ! self::publicAddress($ip))) {
            return [];
        }
        try {
            $response = Http::timeout(4)->connectTimeout(2)->withOptions([
                'allow_redirects' => false, 'stream' => true, 'proxy' => '',
                'curl' => [CURLOPT_RESOLVE => [$host.':443:'.$addresses[0]]],
            ])->withHeaders(['Accept' => 'text/html'])->get($url);
            if (! $response->successful() || ! str_contains(strtolower($response->header('Content-Type')), 'text/html')) {
                $response->toPsrResponse()->getBody()->close();

                return [];
            }
            $stream = $response->toPsrResponse()->getBody();
            try {
                $html = $stream->read(65536);
            } finally {
                $stream->close();
            }
            $dom = new \DOMDocument;
            @$dom->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($dom);
            $title = $xpath->evaluate('string(//meta[@property="og:title"]/@content)') ?: $xpath->evaluate('string(//title)');
            $description = $xpath->evaluate('string(//meta[@property="og:description"]/@content)') ?: $xpath->evaluate('string(//meta[@name="description"]/@content)');

            return ['title' => mb_substr(trim(strip_tags($title)), 0, 255), 'description' => mb_substr(trim(strip_tags($description)), 0, 240)];
        } catch (\Throwable) {
            return [];
        }
    }

    public static function publicAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
            && ! IpUtils::checkIp($ip, ['0.0.0.0/8', '100.64.0.0/10', '169.254.0.0/16', '192.0.0.0/24', '192.0.2.0/24', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4', '240.0.0.0/4']);
    }
}
