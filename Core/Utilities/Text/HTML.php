<?php

declare(strict_types=1);

namespace Core\Utilities\Text;

/**
 * HTML Minifier and Processor
 *
 * A comprehensive HTML minifier that removes unnecessary characters, whitespace,
 * and optimizes inline content while preserving functionality. Features modular
 * design for easy maintenance and extensibility.
 *
 * Key features:
 * - Minifies HTML attributes and whitespace
 * - Processes inline CSS in style attributes
 * - Processes CSS in <style> blocks
 * - Processes JavaScript in <script> blocks
 * - Removes unnecessary HTML comments
 * - Preserves important content and structure
 *
 * @author Prima Yoga
 */
class HTML
{
    /**
     * CSS minifier instance for processing styles
     */
    protected $css;

    /**
     * JavaScript minifier instance for processing scripts
     */
    protected $js;

    /**
     * Constructor - Initialize HTML processor
     */
    public function __construct()
    {
        // Initialize without dependencies for now
    }

    /**
     * Set HTTP headers for HTML output
     */
    private function setHeaders(): void
    {
        header('Content-Type: text/html; charset=UTF-8');
    }

    /**
     * Minify HTML content with optional CSS and JS processing
     *
     * @param string $input HTML content to minify
     * @param bool $processJS Whether to minify JavaScript blocks
     * @param bool $processCSS Whether to minify CSS blocks and inline styles
     * @return string Minified HTML content
     */
    public function minify(string $input, bool $processJS = true, bool $processCSS = true): string
    {
        $this->setHeaders();

        if (trim($input) === "") {
            return $input;
        }

        // Apply minification steps in logical order
        $output = $this->normalizeLineEndings($input);
        $output = $this->minifyAttributes($output);
        $output = $this->minifyComments($output);

        if ($processCSS) {
            $output = $this->minifyInlineCSS($output);
            $output = $this->minifyCSSBlocks($output);
        }

        if ($processJS) {
            $output = $this->minifyJSBlocks($output);
        }

        $output = $this->minifyWhitespace($output);

        return $output;
    }

    /**
     * Normalize line endings to Unix standard
     */
    private function normalizeLineEndings(string $html): string
    {
        return str_replace("\r\n", "\n", $html);
    }

    /**
     * Minify HTML tag attributes
     *
     * Removes extra whitespace between attributes and normalizes
     * attribute formatting while preserving functionality.
     */
    private function minifyAttributes(string $html): string
    {
        // ?? $html: see minifyWhitespace()'s docblock re: preg_replace()/
        // preg_replace_callback() returning NULL rather than the original string
        // on a PCRE limit - this method's own `: string` return type would
        // otherwise turn that into a TypeError at the return statement.
        return preg_replace_callback(
            '#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s',
            function ($matches) {
                $tagName = $matches[1];
                $attributes = $matches[2] ?? '';
                $selfClosing = $matches[3];

                if (empty($attributes)) {
                    return "<{$tagName}{$selfClosing}>";
                }

                // Normalize attribute spacing
                $normalized = preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $attributes) ?? $attributes;
                $normalized = trim($normalized);

                return "<{$tagName} {$normalized}{$selfClosing}>";
            },
            $html
        ) ?? $html;
    }

    /**
     * Minify HTML comments while preserving IE conditional comments
     */
    private function minifyComments(string $html): string
    {
        // ?? $html: see minifyWhitespace()'s docblock - preg_replace() returns
        // NULL (not the original string) on hitting a PCRE limit on very large
        // input, and this runs against the full page HTML too.
        // Remove standard HTML comments but preserve IE conditional comments
        $html = preg_replace('/<!--(?!\[if\s).*?-->/s', '', $html) ?? $html;

        // Clean up whitespace left by removed comments
        $html = preg_replace('/^\s*?\n\s*/m', '', $html) ?? $html;

        return $html;
    }

    /**
     * Minify inline CSS within style attributes
     */
    private function minifyInlineCSS(string $html): string
    {
        if (strpos($html, ' style=') === false) {
            return $html;
        }

        return preg_replace_callback(
            '#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s',
            function ($matches) {
                $beforeTag = $matches[1];
                $quote = $matches[2];
                $css = $matches[3];

                // Simple CSS minification for inline styles
                $minifiedCSS = $this->minifySimpleCSS($css);

                return "<{$beforeTag} style={$quote}{$minifiedCSS}{$quote}";
            },
            $html
        ) ?? $html;
    }

    /**
     * Minify CSS within <style> blocks
     */
    private function minifyCSSBlocks(string $html): string
    {
        if (strpos($html, '</style>') === false) {
            return $html;
        }

        return preg_replace_callback(
            '#<style(.*?)>(.*?)</style>#is',
            function ($matches) {
                $attributes = $matches[1];
                $css = $matches[2];

                // Simple CSS minification for style blocks
                $minifiedCSS = $this->minifySimpleCSS($css);

                return "<style{$attributes}>{$minifiedCSS}</style>";
            },
            $html
        ) ?? $html;
    }

    /**
     * Minify JavaScript within <script> blocks
     */
    private function minifyJSBlocks(string $html): string
    {
        if (strpos($html, '</script>') === false) {
            return $html;
        }

        return preg_replace_callback(
            '#<script(.*?)>(.*?)</script>#is',
            function ($matches) {
                $attributes = $matches[1];
                $js = $matches[2];

                // Simple JS minification for script blocks
                $minifiedJS = $this->minifySimpleJS($js);

                return "<script{$attributes}>{$minifiedJS}</script>";
            },
            $html
        ) ?? $html;
    }

    /**
     * Simple CSS minification for inline styles
     */
    private function minifySimpleCSS(string $css): string
    {
        // ?? $css: see minifyWhitespace()'s docblock re: preg_replace() returning
        // NULL rather than the original string on a PCRE limit.
        // Basic CSS minification - remove comments and extra whitespace
        $css = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//s', '', $css) ?? $css;
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css) ?? $css;
        $css = preg_replace('/\s+/', ' ', $css) ?? $css;
        return trim($css);
    }

    /**
     * Simple JavaScript minification for script blocks
     */
    private function minifySimpleJS(string $js): string
    {
        // Remove single-line // comments without touching // inside string literals.
        // Strategy: scan for string literals first and preserve them, strip everything else.
        $js = preg_replace_callback(
            '/(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|"[^"\\\\]*(?:\\\\.[^"\\\\]*)*")|\/\/[^\n]*/s',
            static function (array $matches): string {
                // $matches[1] is set and non-empty only when a string literal was matched.
                // When the // comment branch matches, group 1 is unset - coalesce to ''.
                return ($matches[1] ?? '') !== '' ? $matches[1] : '';
            },
            $js
        );
        // Remove block comments /* ... */
        // ?? $js throughout: see minifyWhitespace()'s docblock re: preg_replace()
        // returning NULL rather than the original string on a PCRE limit.
        $js = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//s', '', $js) ?? $js;
        // Compact whitespace around operators and braces
        $js = preg_replace('/\s*([{}:;,>+~=!])\s*/', '$1', $js) ?? $js;
        $js = preg_replace('/\s+/', ' ', $js) ?? $js;
        return trim($js);
    }

    /**
     * Minify HTML whitespace while preserving structure
     *
     * Intelligently removes unnecessary whitespace between HTML tags
     * while maintaining proper formatting and readability.
     */
    private function minifyWhitespace(string $html): string
    {
        // Handle different types of whitespace patterns

        // Every step below falls back to the input unchanged (`?? $html`) instead
        // of overwriting $html with preg_replace()'s return value directly -
        // preg_replace() returns NULL (not false, not the original string) when it
        // hits an internal PCRE limit (backtrack/recursion) rather than throwing,
        // and several of these patterns use non-greedy `.*?` with the `s` modifier
        // across the FULL page HTML, the largest/most limit-prone string in this
        // whole minify pipeline. Once one step returned NULL, every subsequent
        // `preg_replace($pattern, $replacement, $html)` call crashed outright
        // (TypeError: Argument #3 ($subject) must be of type array|string, null
        // given) - confirmed live on a Marketplace Listings edit/create form once
        // its brand/product dropdowns grew past ~13,000 combined `<option>` tags.
        // Skipping a pattern on an oversized page is a silent, harmless
        // degradation (slightly less compact whitespace); crashing the whole
        // admin page is not.

        // Pattern 1: Keep space after self-closing tags like <img> and <input>
        $html = preg_replace('#<(img|input|br|hr|meta|link)(>| .*?>)#s', '<$1$2', $html) ?? $html;

        // Pattern 2: Remove line breaks and multiple spaces between tags
        $html = preg_replace('#(>)(?:\n*|\s{2,})(<)#s', '$1$2', $html) ?? $html;

        // Pattern 3: Remove spaces before closing tags
        $html = preg_replace('#\s+(<\/.*?>)#s', '$1', $html) ?? $html;

        // Pattern 4: Handle tag combinations and spacing
        $html = preg_replace('#(<[^\/]*?>)\s+(<[^\/]*?>)#s', '$1$2', $html) ?? $html;
        $html = preg_replace('#(<\/.*?>)\s+(<\/.*?>)#s', '$1$2', $html) ?? $html;

        // Pattern 5: Handle spacing around content
        $html = preg_replace('#(<\/.*?>)\s+(\s)(?!\<)#s', '$1$2', $html) ?? $html;
        $html = preg_replace('#(?<!\>)\s+(\s)(<[^\/]*?\/?>)#s', '$1$2', $html) ?? $html;

        // Pattern 6: Handle empty tags
        $html = preg_replace('#(<[^\/]*?>)\s+(<\/.*?>)#s', '$1$2', $html) ?? $html;

        // Pattern 7: Clean up multiple spaces and &nbsp;
        $html = preg_replace('#(&nbsp;)&nbsp;(?![<\s])#s', '$1 ', $html) ?? $html;
        $html = preg_replace('#(?<=\>)(&nbsp;)(?=\<)#s', '$1', $html) ?? $html;

        // Final cleanup: remove leading/trailing whitespace
        $html = trim($html);

        return $html;
    }
}
