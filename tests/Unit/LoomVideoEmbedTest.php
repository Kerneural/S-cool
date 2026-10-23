<?php

namespace Tests\Unit;

use App\Services\VideoEmbedService;
use PHPUnit\Framework\TestCase;

class LoomVideoEmbedTest extends TestCase
{
    public function test_loom_urls_are_normalized_to_fixed_provider_hosts(): void
    {
        $id = 'e0838a4ac97647beb59ed491e6407579';
        foreach (["https://loom.com/share/$id", "https://www.loom.com/embed/$id?sid=demo#player"] as $url) {
            $this->assertSame('loom', VideoEmbedService::parse($url)['provider']);
            $this->assertSame("https://www.loom.com/embed/$id", VideoEmbedService::getEmbedUrl($url));
            $this->assertSame("https://www.loom.com/share/$id", VideoEmbedService::getProviderUrl($url));
        }
    }

    public function test_invalid_loom_urls_never_become_embeds(): void
    {
        $id = 'e0838a4ac97647beb59ed491e6407579';
        foreach ([
            "http://loom.com/share/$id", "https://loom.com.evil.test/share/$id",
            "https://evil.test/loom.com/share/$id", "https://user@loom.com/share/$id",
            "https://loom.com:8443/share/$id", "https://loom.com/share/$id/extra",
            'https://loom.com/share/not-a-video', "https://loom.com/share/$id?sid=a\nb",
            '<iframe src="https://loom.com/share/'.$id.'"></iframe>',
        ] as $url) {
            $this->assertNull(VideoEmbedService::parse($url), $url);
        }
    }
}
