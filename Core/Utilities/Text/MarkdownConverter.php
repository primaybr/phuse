<?php

declare(strict_types=1);

namespace Core\Utilities\Text;

/**
 * Converts server-rendered public HTML into compact, agent-readable Markdown.
 */
class MarkdownConverter
{
    /** @var list<string> */
    private const REMOVED_ELEMENTS = [
        'nav', 'header', 'footer', 'form', 'dialog', 'script', 'style',
        'template', 'svg', 'canvas', 'noscript', 'iframe', 'button', 'input',
        'select', 'textarea',
    ];

    public function convert(string $html): string
    {
        if (trim($html) === '' || !class_exists(\DOMDocument::class)) {
            return '';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="utf-8" ?>' . $html,
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }

        if ($loaded !== true) {
            return '';
        }

        $metadata = $this->metadata($document);
        $jsonLd = $this->jsonLd($document);
        $root = $this->contentRoot($document);

        if (!$root instanceof \DOMNode) {
            return $this->compose($metadata, '', $jsonLd);
        }

        $this->removeChrome($root);
        $body = trim($this->renderBlockChildren($root));
        $body = preg_replace("/\n{3,}/", "\n\n", $body) ?? $body;

        return $this->compose($metadata, $body, $jsonLd);
    }

    /** @return array{title?:string, description?:string, image?:string} */
    private function metadata(\DOMDocument $document): array
    {
        $standard = [];
        $openGraph = [];

        foreach ($document->getElementsByTagName('meta') as $meta) {
            $content = trim($meta->getAttribute('content'));
            if ($content === '') {
                continue;
            }

            $name = strtolower(trim($meta->getAttribute('name')));
            $property = strtolower(trim($meta->getAttribute('property')));
            if (in_array($name, ['title', 'description'], true) && !isset($standard[$name])) {
                $standard[$name] = $content;
            }
            if (in_array($property, ['og:title', 'og:description', 'og:image'], true) && !isset($openGraph[$property])) {
                $openGraph[$property] = $content;
            }
        }

        $title = $standard['title'] ?? $openGraph['og:title'] ?? '';
        if ($title === '') {
            $titleNode = $document->getElementsByTagName('title')->item(0);
            $title = trim((string) ($titleNode?->textContent ?? ''));
        }

        $result = [];
        if ($title !== '') {
            $result['title'] = $title;
        }

        $description = $standard['description'] ?? $openGraph['og:description'] ?? '';
        if ($description !== '') {
            $result['description'] = $description;
        }
        if (($openGraph['og:image'] ?? '') !== '') {
            $result['image'] = $openGraph['og:image'];
        }

        return $result;
    }

    /** @return list<string> */
    private function jsonLd(\DOMDocument $document): array
    {
        $blocks = [];
        foreach ($document->getElementsByTagName('script') as $script) {
            if (strtolower(trim($script->getAttribute('type'))) !== 'application/ld+json') {
                continue;
            }

            try {
                $decoded = json_decode(trim((string) $script->textContent), true, 512, JSON_THROW_ON_ERROR);
                $encoded = json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if (is_string($encoded)) {
                    $blocks[] = $encoded;
                }
            } catch (\JsonException) {
                // Invalid structured data must not break the readable page body.
            }
        }

        return $blocks;
    }

    private function contentRoot(\DOMDocument $document): ?\DOMNode
    {
        foreach (['main', 'article', 'body'] as $tag) {
            $node = $document->getElementsByTagName($tag)->item(0);
            if ($node instanceof \DOMNode) {
                return $node;
            }
        }

        return $document->documentElement;
    }

    private function removeChrome(\DOMNode $root): void
    {
        if (!$root->ownerDocument instanceof \DOMDocument) {
            return;
        }

        foreach (self::REMOVED_ELEMENTS as $tag) {
            $matches = [];
            foreach ($root->ownerDocument->getElementsByTagName($tag) as $node) {
                if ($node !== $root && $this->isWithin($node, $root)) {
                    $matches[] = $node;
                }
            }
            foreach ($matches as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    private function isWithin(\DOMNode $node, \DOMNode $root): bool
    {
        for ($parent = $node->parentNode; $parent instanceof \DOMNode; $parent = $parent->parentNode) {
            if ($parent === $root) {
                return true;
            }
        }
        return false;
    }

    private function renderBlockChildren(\DOMNode $parent): string
    {
        $markdown = '';
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $text = $this->plainText($child->nodeValue ?? '');
                if ($text !== '') {
                    $markdown .= $text . "\n\n";
                }
                continue;
            }
            if ($child instanceof \DOMElement) {
                $markdown .= $this->renderBlock($child);
            }
        }
        return $markdown;
    }

    private function renderBlock(\DOMElement $element): string
    {
        $tag = strtolower($element->tagName);

        if (preg_match('/^h([1-6])$/', $tag, $match)) {
            $text = $this->inlineChildren($element);
            return $text === '' ? '' : str_repeat('#', (int) $match[1]) . ' ' . $text . "\n\n";
        }

        return match ($tag) {
            'p' => $this->paragraph($element),
            'ul' => $this->renderList($element, false),
            'ol' => $this->renderList($element, true),
            'blockquote' => $this->renderBlockquote($element),
            'pre' => $this->renderPreformatted($element),
            'table' => $this->renderTable($element),
            'hr' => "---\n\n",
            'img' => $this->inlineNode($element) . "\n\n",
            'main', 'article', 'section', 'div', 'aside', 'figure', 'figcaption', 'body' => $this->renderContainer($element),
            default => $this->renderContainer($element),
        };
    }

    private function paragraph(\DOMElement $element): string
    {
        $text = $this->inlineChildren($element);
        return $text === '' ? '' : $text . "\n\n";
    }

    private function renderContainer(\DOMElement $element): string
    {
        $hasBlock = false;
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement && $this->isBlockTag(strtolower($child->tagName))) {
                $hasBlock = true;
                break;
            }
        }

        if ($hasBlock) {
            return $this->renderBlockChildren($element);
        }

        return $this->paragraph($element);
    }

    private function isBlockTag(string $tag): bool
    {
        return preg_match('/^h[1-6]$/', $tag) === 1 || in_array($tag, [
            'p', 'div', 'section', 'article', 'main', 'aside', 'figure', 'ul', 'ol',
            'blockquote', 'pre', 'table', 'hr',
        ], true);
    }

    private function renderList(\DOMElement $list, bool $ordered): string
    {
        $lines = [];
        $number = 1;
        foreach ($list->childNodes as $child) {
            if (!$child instanceof \DOMElement || strtolower($child->tagName) !== 'li') {
                continue;
            }

            $content = $this->inlineChildren($child, ['ul', 'ol']);
            if ($content !== '') {
                $lines[] = ($ordered ? $number . '. ' : '- ') . $content;
            }
            $number++;
        }

        return $lines === [] ? '' : implode("\n", $lines) . "\n\n";
    }

    private function renderBlockquote(\DOMElement $element): string
    {
        $content = trim($this->renderBlockChildren($element));
        if ($content === '') {
            $content = $this->inlineChildren($element);
        }
        if ($content === '') {
            return '';
        }

        return preg_replace('/^/m', '> ', $content) . "\n\n";
    }

    private function renderPreformatted(\DOMElement $element): string
    {
        $codeNode = null;
        foreach ($element->childNodes as $child) {
            if ($child instanceof \DOMElement && strtolower($child->tagName) === 'code') {
                $codeNode = $child;
                break;
            }
        }

        $code = trim((string) ($codeNode?->textContent ?? $element->textContent), "\r\n");
        if ($code === '') {
            return '';
        }

        $language = '';
        if ($codeNode instanceof \DOMElement && preg_match('/(?:^|\s)language-([a-z0-9_+-]+)/i', $codeNode->getAttribute('class'), $match)) {
            $language = strtolower($match[1]);
        }

        $fence = '```';
        if (preg_match_all('/`+/', $code, $matches) > 0) {
            $longest = max(array_map('strlen', $matches[0]));
            $fence = str_repeat('`', max(3, $longest + 1));
        }

        return $fence . $language . "\n" . $code . "\n" . $fence . "\n\n";
    }

    private function renderTable(\DOMElement $table): string
    {
        $rows = [];
        $columns = 0;
        foreach ($table->getElementsByTagName('tr') as $row) {
            $cells = [];
            foreach ($row->childNodes as $cell) {
                if (!$cell instanceof \DOMElement || !in_array(strtolower($cell->tagName), ['th', 'td'], true)) {
                    continue;
                }
                $cells[] = str_replace('|', '\\|', $this->inlineChildren($cell));
            }
            if ($cells !== []) {
                $columns = max($columns, count($cells));
                $rows[] = $cells;
            }
        }

        if ($rows === [] || $columns === 0) {
            return '';
        }

        foreach ($rows as &$row) {
            $row = array_pad($row, $columns, '');
        }
        unset($row);

        $lines = ['| ' . implode(' | ', $rows[0]) . ' |'];
        $lines[] = '| ' . implode(' | ', array_fill(0, $columns, '---')) . ' |';
        foreach (array_slice($rows, 1) as $row) {
            $lines[] = '| ' . implode(' | ', $row) . ' |';
        }

        return implode("\n", $lines) . "\n\n";
    }

    /** @param list<string> $excludedTags */
    private function inlineChildren(\DOMNode $parent, array $excludedTags = []): string
    {
        $text = '';
        $lastImageAlt = null;
        foreach ($parent->childNodes as $child) {
            if ($child instanceof \DOMElement && in_array(strtolower($child->tagName), $excludedTags, true)) {
                continue;
            }

            if ($child instanceof \DOMElement && strtolower($child->tagName) === 'img') {
                $alt = $this->plainText($child->getAttribute('alt'));
                if ($alt !== '' && $lastImageAlt === $alt) {
                    continue;
                }
                $lastImageAlt = $alt !== '' ? $alt : null;
            } elseif (!$child instanceof \DOMText || trim((string) $child->nodeValue) !== '') {
                $lastImageAlt = null;
            }

            $text .= $this->inlineNode($child);
        }

        $text = preg_replace('/[ \t\f\v]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */u', "\n", $text) ?? $text;
        return trim($text);
    }

    private function inlineNode(\DOMNode $node): string
    {
        if ($node instanceof \DOMText) {
            return $this->escapedText($node->nodeValue ?? '');
        }
        if (!$node instanceof \DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);
        $content = $this->inlineChildren($node);

        return match ($tag) {
            'strong', 'b' => $content === '' ? '' : '**' . $content . '**',
            'em', 'i' => $content === '' ? '' : '*' . $content . '*',
            'del', 's', 'strike' => $content === '' ? '' : '~~' . $content . '~~',
            'code' => $this->inlineCode($node->textContent),
            'a' => $this->inlineLink($node, $content),
            'img' => $this->inlineImage($node),
            'br' => "  \n",
            default => $content,
        };
    }

    private function inlineCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }
        $fence = str_contains($code, '`') ? '``' : '`';
        return $fence . $code . $fence;
    }

    private function inlineLink(\DOMElement $element, string $label): string
    {
        $href = trim($element->getAttribute('href'));
        if ($label === '' || $href === '') {
            return $label;
        }
        return '[' . $label . '](' . str_replace([' ', ')'], ['%20', '\\)'], $href) . ')';
    }

    private function inlineImage(\DOMElement $element): string
    {
        $alt = $this->plainText($element->getAttribute('alt'));
        $src = trim($element->getAttribute('src'));
        if ($alt === '' || $src === '') {
            return '';
        }
        return '![' . str_replace([']', '['], ['\\]', '\\['], $alt) . '](' . str_replace([' ', ')'], ['%20', '\\)'], $src) . ')';
    }

    private function escapedText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return str_replace(['\\', '*', '_', '[', ']', '`'], ['\\\\', '\\*', '\\_', '\\[', '\\]', '\\`'], $text);
    }

    private function plainText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /**
     * @param array{title?:string, description?:string, image?:string} $metadata
     * @param list<string> $jsonLd
     */
    private function compose(array $metadata, string $body, array $jsonLd): string
    {
        $parts = [];
        if ($metadata !== []) {
            $frontmatter = ['---'];
            foreach (['title', 'description', 'image'] as $field) {
                if (!isset($metadata[$field])) {
                    continue;
                }
                $value = json_encode($metadata[$field], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if (is_string($value)) {
                    $frontmatter[] = $field . ': ' . $value;
                }
            }
            $frontmatter[] = '---';
            $parts[] = implode("\n", $frontmatter);
        }

        if ($body !== '') {
            $parts[] = $body;
        }
        if ($jsonLd !== []) {
            $parts[] = "```json\n" . implode("\n", $jsonLd) . "\n```";
        }

        return $parts === [] ? '' : implode("\n\n", $parts) . "\n";
    }
}
