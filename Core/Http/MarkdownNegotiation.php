<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * Selects explicit Markdown requests for public, read-only pages.
 */
final class MarkdownNegotiation
{
    /** @var list<string> */
    private const EXCLUDED_PATHS = [
        '/admin', '/api', '/setup', '/health',
        '/sitemap.xml', '/robots.txt', '/llms.txt',
    ];

    /** @var list<string> */
    private const EXCLUDED_SUFFIXES = ['/feed.rss', '/feed.rst'];

    /**
     * @param array<string, mixed> $server
     */
    public static function isRequested(array $server): bool
    {
        if (strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            return false;
        }

        $path = parse_url((string) ($server['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $path = strtolower(is_string($path) && $path !== '' ? $path : '/');
        $path = self::withoutLocalePrefix($path);

        foreach (self::EXCLUDED_PATHS as $excluded) {
            if ($path === $excluded || str_starts_with($path, $excluded . '/')) {
                return false;
            }
        }
        foreach (self::EXCLUDED_SUFFIXES as $excluded) {
            if (str_ends_with($path, $excluded)) {
                return false;
            }
        }

        $accept = trim((string) ($server['HTTP_ACCEPT'] ?? ''));
        if ($accept === '') {
            return false;
        }

        foreach (explode(',', $accept) as $range) {
            $parts = array_map('trim', explode(';', $range));
            if (strtolower((string) array_shift($parts)) !== 'text/markdown') {
                continue;
            }

            $quality = 1.0;
            foreach ($parts as $parameter) {
                if (!str_contains($parameter, '=')) {
                    continue;
                }

                [$name, $value] = array_map('trim', explode('=', $parameter, 2));
                if (strtolower($name) !== 'q') {
                    continue;
                }

                if (!preg_match('/^(?:0(?:\.\d{0,3})?|1(?:\.0{0,3})?)$/', $value)) {
                    $quality = 0.0;
                    break;
                }
                $quality = (float) $value;
            }

            if ($quality > 0.0) {
                return true;
            }
        }

        return false;
    }

    private static function withoutLocalePrefix(string $path): string
    {
        if (!preg_match('#^/[a-z]{2}(?:-[a-z0-9]+)?(?=/|$)(.*)$#i', $path, $matches)) {
            return $path;
        }

        $remainder = (string) ($matches[1] ?? '');
        return $remainder === '' ? '/' : $remainder;
    }
}
