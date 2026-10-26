<?php

namespace App\Support;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Inline\Text;

/**
 * The only boundary allowed to turn stored lesson text into HTML.
 *
 * Raw HTML is stripped, unsafe link schemes are dropped and parser nesting is
 * bounded. Images are rendered as plain links so Markdown cannot embed remote
 * trackers or bypass the private-media routes. Content headings start at h2
 * because the lesson page owns its single h1.
 */
class LessonMarkdown
{
    private static ?MarkdownConverter $converter = null;

    public static function toHtml(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        return self::converter()->convert($markdown)->getContent();
    }

    private static function converter(): MarkdownConverter
    {
        if (self::$converter !== null) {
            return self::$converter;
        }

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'max_delimiters_per_line' => 1000,
            'renderer' => ['soft_break' => "<br>\n"],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addEventListener(DocumentParsedEvent::class, [self::class, 'normalize']);

        return self::$converter = new MarkdownConverter($environment);
    }

    public static function normalize(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        $headings = [];
        $images = [];
        foreach ($document->iterator() as $node) {
            if ($node instanceof Heading) {
                $headings[] = $node;
            } elseif ($node instanceof Image) {
                $images[] = $node;
            }
        }

        if ($headings !== []) {
            $shift = max(0, 2 - min(array_map(fn (Heading $heading) => $heading->getLevel(), $headings)));
            foreach ($headings as $heading) {
                $heading->setLevel(min(6, $heading->getLevel() + $shift));
            }
        }

        foreach ($images as $image) {
            $link = new Link($image->getUrl(), null, $image->getTitle());
            foreach ($image->children() as $child) {
                $link->appendChild($child);
            }
            if ($link->firstChild() === null) {
                $link->appendChild(new Text(__('Image link')));
            }
            $image->replaceWith($link);
        }
    }
}
