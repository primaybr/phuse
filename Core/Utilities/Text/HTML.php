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
                $attributes = preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $attributes);
                $attributes = trim($attributes);

                return "<{$tagName} {$attributes}{$selfClosing}>";
            },
            $html
        );
    }

    /**
     * Minify HTML comments while preserving IE conditional comments
     */
    private function minifyComments(string $html): string
    {
        // Remove standard HTML comments but preserve IE conditional comments
        $html = preg_replace('/<!--(?!\[if\s).*?-->/s', '', $html);

        // Clean up whitespace left by removed comments
        $html = preg_replace('/^\s*?\n\s*/m', '', $html);

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
        );
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
        );
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
        );
    }

    /**
     * Simple CSS minification for inline styles
     */
    private function minifySimpleCSS(string $css): string
    {
        // Basic CSS minification - remove comments and extra whitespace
        $css = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//s', '', $css);
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        return trim($css);
    }

    /**
     * Simple JavaScript minification for script blocks
     */
    private function minifySimpleJS(string $js): string
    {
        // Basic JS minification - remove comments and extra whitespace
        $js = preg_replace('/\/\/.*$/m', '', $js);
        $js = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//s', '', $js);
        $js = preg_replace('/\s*([{}:;,>+~=!])\s*/', '$1', $js);
        $js = preg_replace('/\s+/', ' ', $js);
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

        // Pattern 1: Keep space after self-closing tags like <img> and <input>
        $html = preg_replace('#<(img|input|br|hr|meta|link)(>| .*?>)#s', '<$1$2', $html);

        // Pattern 2: Remove line breaks and multiple spaces between tags
        $html = preg_replace('#(>)(?:\n*|\s{2,})(<)#s', '$1$2', $html);

        // Pattern 3: Remove spaces before closing tags
        $html = preg_replace('#\s+(<\/.*?>)#s', '$1', $html);

        // Pattern 4: Handle tag combinations and spacing
        $html = preg_replace('#(<[^\/]*?>)\s+(<[^\/]*?>)#s', '$1$2', $html);
        $html = preg_replace('#(<\/.*?>)\s+(<\/.*?>)#s', '$1$2', $html);

        // Pattern 5: Handle spacing around content
        $html = preg_replace('#(<\/.*?>)\s+(\s)(?!\<)#s', '$1$2', $html);
        $html = preg_replace('#(?<!\>)\s+(\s)(<[^\/]*?\/?>)#s', '$1$2', $html);

        // Pattern 6: Handle empty tags
        $html = preg_replace('#(<[^\/]*?>)\s+(<\/.*?>)#s', '$1$2', $html);

        // Pattern 7: Clean up multiple spaces and &nbsp;
        $html = preg_replace('#(&nbsp;)&nbsp;(?![<\s])#s', '$1 ', $html);
        $html = preg_replace('#(?<=\>)(&nbsp;)(?=\<)#s', '$1', $html);

        // Final cleanup: remove leading/trailing whitespace
        $html = trim($html);

        return $html;
    }
}
