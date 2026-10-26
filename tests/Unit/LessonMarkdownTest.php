<?php

namespace Tests\Unit;

use App\Support\LessonMarkdown;
use Tests\TestCase;

class LessonMarkdownTest extends TestCase
{
    public function test_valid_markdown_becomes_semantic_content_below_the_page_heading(): void
    {
        $html = LessonMarkdown::toHtml("# Title\n\n## **Section**\n\n- one\n- two\n\n> quote\n\n| a | b |\n|---|---|\n| 1 | 2 |\n\nUse `rg` and [docs](https://example.test/a).");

        $this->assertStringContainsString('<h2>Title</h2>', $html);
        $this->assertStringContainsString('<h3><strong>Section</strong></h3>', $html);
        $this->assertStringNotContainsString('<h1', $html);
        $this->assertStringContainsString('<li>two</li>', $html);
        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringContainsString('<td>2</td>', $html);
        $this->assertStringContainsString('<code>rg</code>', $html);
        $this->assertStringContainsString('<a href="https://example.test/a">docs</a>', $html);
    }

    public function test_plain_text_keeps_line_breaks_and_mixed_language(): void
    {
        $html = LessonMarkdown::toHtml("Dòng một tiếng Việt\nSecond line in English");

        $this->assertSame("<p>Dòng một tiếng Việt<br>\nSecond line in English</p>\n", $html);
        $this->assertSame('', LessonMarkdown::toHtml(null));
        $this->assertSame('', LessonMarkdown::toHtml("  \n"));
    }

    public function test_fenced_code_is_escaped_and_whitespace_preserved(): void
    {
        $html = LessonMarkdown::toHtml("```php\n<?php\n    echo '<b>';\n```");

        $this->assertStringContainsString("<pre><code class=\"language-php\">&lt;?php\n    echo '&lt;b&gt;';\n</code></pre>", $html);
    }

    public function test_raw_html_handlers_embeds_and_unsafe_schemes_cannot_execute(): void
    {
        $html = LessonMarkdown::toHtml(implode("\n\n", [
            '<script>alert(1)</script>',
            '<img src=x onerror=alert(1)>',
            '<iframe src="https://evil.test"></iframe>',
            '<b onclick="alert(1)">bold</b>',
            '[js](javascript:alert(1)) [data](data:text/html;base64,PHNjcmlwdD4=) [vb](vbscript:x)',
            '<svg onload=alert(1)></svg>',
        ]));

        foreach (['<script', '<iframe', '<img', '<svg', 'onerror', 'onclick', 'onload', 'javascript:', 'vbscript:', 'data:text/html', '<b'] as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $html);
        }
        $this->assertStringContainsString('<a>js</a>', $html);
    }

    public function test_images_render_as_plain_links_so_markdown_cannot_embed_media(): void
    {
        $html = LessonMarkdown::toHtml('![Diagram](https://cdn.test/d.png) ![](https://cdn.test/e.png) ![x](javascript:alert(1))');

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('<a href="https://cdn.test/d.png">Diagram</a>', $html);
        $this->assertStringContainsString('<a href="https://cdn.test/e.png">Image link</a>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_task_list_items_are_read_only(): void
    {
        $html = LessonMarkdown::toHtml("- [x] done\n- [ ] todo");

        $this->assertSame(2, substr_count($html, 'disabled=""'));
    }

    public function test_deep_nesting_is_bounded(): void
    {
        $html = LessonMarkdown::toHtml(str_repeat('> ', 500).'deep');

        $this->assertLessThanOrEqual(21, substr_count($html, '<blockquote>'));
    }
}
