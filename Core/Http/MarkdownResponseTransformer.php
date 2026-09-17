<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Utilities\Text\MarkdownConverter;

/**
 * Builds a Markdown response body and representation-specific header changes.
 */
final class MarkdownResponseTransformer
{
    /** @var list<string> */
    private const REMOVED_HEADERS = [
        'Content-Length',
        'Content-Encoding',
        'Content-Range',
        'Transfer-Encoding',
        'ETag',
        'Last-Modified',
    ];

    public function __construct(private readonly MarkdownConverter $converter = new MarkdownConverter())
    {
    }

    /**
     * @param list<string> $headers
     * @return array{body:string, headers:array<string, string>, remove:list<string>}|null
     */
    public function transform(string $body, int $status, array $headers): ?array
    {
        if (trim($body) === '' || !$this->statusAllowsBody($status) || !$this->isHtml($body, $headers)) {
            return null;
        }

        $markdown = $this->converter->convert($body);
        if ($markdown === '') {
            return null;
        }

        return [
            'body' => $markdown,
            'headers' => [
                'Content-Type' => 'text/markdown; charset=utf-8',
                'Vary' => $this->vary($headers),
                'X-Markdown-Tokens' => (string) $this->tokenEstimate($markdown),
            ],
            'remove' => self::REMOVED_HEADERS,
        ];
    }

    private function statusAllowsBody(int $status): bool
    {
        if ($status >= 100 && $status < 200) {
            return false;
        }
        if ($status >= 300 && $status < 400) {
            return false;
        }
        return !in_array($status, [204, 205, 304], true);
    }

    /** @param list<string> $headers */
    private function isHtml(string $body, array $headers): bool
    {
        $contentType = $this->headerValues($headers, 'Content-Type');
        if ($contentType !== []) {
            $mediaType = strtolower(trim(explode(';', end($contentType), 2)[0]));
            return $mediaType === 'text/html';
        }

        return preg_match('/^\s*(?:<!doctype\s+html\b|<html\b|<head\b|<body\b|<main\b|<article\b)/i', $body) === 1;
    }

    /** @param list<string> $headers */
    private function vary(array $headers): string
    {
        $dimensions = [];
        foreach ($this->headerValues($headers, 'Vary') as $value) {
            foreach (explode(',', $value) as $dimension) {
                $dimension = trim($dimension);
                if ($dimension !== '') {
                    $dimensions[] = $dimension;
                }
            }
        }

        foreach ($dimensions as $dimension) {
            if ($dimension === '*') {
                return '*';
            }
            if (strcasecmp($dimension, 'Accept') === 0) {
                return implode(', ', $dimensions);
            }
        }

        $dimensions[] = 'Accept';
        return implode(', ', $dimensions);
    }

    /**
     * @param list<string> $headers
     * @return list<string>
     */
    private function headerValues(array $headers, string $name): array
    {
        $values = [];
        foreach ($headers as $header) {
            if (!str_contains($header, ':')) {
                continue;
            }
            [$headerName, $value] = explode(':', $header, 2);
            if (strcasecmp(trim($headerName), $name) === 0) {
                $values[] = trim($value);
            }
        }
        return $values;
    }

    private function tokenEstimate(string $markdown): int
    {
        $characters = preg_match_all('/./us', $markdown);
        $length = $characters === false ? strlen($markdown) : $characters;
        return max(1, (int) ceil($length / 4));
    }
}
