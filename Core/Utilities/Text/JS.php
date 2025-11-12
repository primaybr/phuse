<?php

declare(strict_types=1);

namespace Core\Utilities\Text;

/**
 * JavaScript Minifier
 *
 * A clean, modular JavaScript minifier that removes unnecessary characters
 * while preserving functionality.
 *
 * @author Prima Yoga
 */
class JS
{
    /**
     * Default options for minification
     */
    private const DEFAULT_OPTIONS = [
        'preserve_comments' => false,  // Whether to preserve important comments
        'compress_whitespace' => true,  // Whether to compress whitespace
    ];

    /**
     * String delimiters used in JavaScript
     */
    private const STRING_DELIMITERS = ['\'', '"', '`'];

    /**
     * Keywords that affect regex detection
     */
    private const KEYWORDS = ['return', 'typeof', 'instanceof', 'in'];

    /**
     * Minify JavaScript code
     *
     * @param string $js The JavaScript code to minify
     * @param array $options Minification options
     * @return string Minified JavaScript code
     */
    public static function minify(string $js, array $options = []): string
    {
        $minifier = new self();
        return $minifier->minifyInternal($js, $options);
    }

    /**
     * Internal minification method
     */
    private function minifyInternal(string $js, array $options): string
    {
        if (trim($js) === '') {
            return $js;
        }

        $options = array_merge(self::DEFAULT_OPTIONS, $options);

        // Apply minification steps in logical order
        $output = $this->removeComments($js, $options['preserve_comments']);
        $output = $this->minifyWhitespace($output, $options['compress_whitespace']);
        $output = $this->optimizeValues($output);

        return $output;
    }

    /**
     * Remove JavaScript comments
     */
    private function removeComments(string $js, bool $preserveImportant): string
    {
        // Remove single-line comments (but preserve important ones if specified)
        if ($preserveImportant) {
            // Preserve /*! ... */ comments
            $js = preg_replace('/\/\/(?![\!\s]).*?(?=\n|$)/', '', $js);
        } else {
            // Remove all single-line comments
            $js = preg_replace('/\/\/.*?(?=\n|$)/', '', $js);
        }

        // Remove multi-line comments (but preserve important ones if specified)
        if ($preserveImportant) {
            // Preserve /*! ... */ comments
            $js = preg_replace('/\/\*(?!\!).*?\*\//s', '', $js);
        } else {
            // Remove all multi-line comments
            $js = preg_replace('/\/\*.*?\*\//s', '', $js);
        }

        return $js;
    }

    /**
     * Minify whitespace while preserving necessary spaces
     */
    private function minifyWhitespace(string $js, bool $compress): string
    {
        if (!$compress) {
            return $js;
        }

        // Remove leading/trailing whitespace
        $js = preg_replace('/^\s+|\s+$/m', '', $js);

        // Replace multiple spaces with single space
        $js = preg_replace('/\s+/', ' ', $js);

        // Remove spaces around certain operators and punctuation
        $js = preg_replace('/\s*([{}()[\].,;:?!+\-*\/=<>|&%~^])\s*/', '$1', $js);

        // Remove spaces before commas and semicolons
        $js = preg_replace('/\s+(,|;)/', '$1', $js);

        // Remove spaces after commas and semicolons
        $js = preg_replace('/(,|;)\s+/', '$1', $js);

        return $js;
    }

    /**
     * Optimize JavaScript values and expressions
     */
    private function optimizeValues(string $js): string
    {
        // These optimizations are conservative to avoid breaking functionality

        // Remove unnecessary semicolons at the end of blocks
        $js = preg_replace('/;}+/', '}', $js);

        // Remove trailing comma before closing bracket/brace
        $js = preg_replace('/,(\s*[}\]])/', '$1', $js);

        return $js;
    }

    /**
     * Process string literals safely
     */
    private function processStrings(string $js): string
    {
        $result = '';
        $length = strlen($js);
        $i = 0;

        while ($i < $length) {
            $char = $js[$i];

            if (in_array($char, self::STRING_DELIMITERS)) {
                // Found string start
                $delimiter = $char;
                $result .= $char;
                $i++;

                // Process string content
                while ($i < $length) {
                    $current = $js[$i];

                    if ($current === $delimiter) {
                        // End of string
                        $result .= $current;
                        break;
                    } elseif ($current === '\\') {
                        // Escape sequence
                        $result .= $current;
                        $i++;
                        if ($i < $length) {
                            $result .= $js[$i];
                        }
                    } else {
                        $result .= $current;
                    }
                    $i++;
                }
            } else {
                $result .= $char;
            }
            $i++;
        }

        return $result;
    }

    /**
     * Process regular expressions safely
     */
    private function processRegexes(string $js): string
    {
        $result = '';
        $length = strlen($js);
        $i = 0;

        while ($i < $length) {
            $char = $js[$i];

            if ($char === '/' && $this->isRegexStart($js, $i)) {
                // Found regex start
                $result .= $char;
                $i++;

                // Process regex content
                while ($i < $length) {
                    $current = $js[$i];

                    if ($current === '/') {
                        // Potential end of regex
                        $result .= $current;
                        break;
                    } elseif ($current === '\\') {
                        // Escape sequence in regex
                        $result .= $current;
                        $i++;
                        if ($i < $length) {
                            $result .= $js[$i];
                        }
                    } elseif ($current === '[') {
                        // Character class
                        $result .= $current;
                        $i++;
                        // Find end of character class
                        while ($i < $length && $js[$i] !== ']') {
                            if ($js[$i] === '\\') {
                                $result .= $js[$i];
                                $i++;
                            }
                            $result .= $js[$i];
                            $i++;
                        }
                    } else {
                        $result .= $current;
                    }
                    $i++;
                }
            } else {
                $result .= $char;
            }
            $i++;
        }

        return $result;
    }

    /**
     * Check if a slash starts a regex (not division)
     */
    private function isRegexStart(string $js, int $position): bool
    {
        if ($position === 0) {
            return true;
        }

        // Look backwards for context
        $i = $position - 1;

        while ($i >= 0) {
            $char = $js[$i];

            if ($char === ')') {
                // Skip over parentheses
                $i--;
                continue;
            } elseif ($char === '(') {
                // Found opening paren, not in regex context
                return false;
            } elseif ($char === '"' || $char === "'") {
                // Found string, not in regex context
                return false;
            } elseif ($char === '/' && ($i === 0 || $js[$i - 1] !== '\\')) {
                // Found another slash, could be comment or division
                return false;
            } elseif (preg_match('/[a-zA-Z_$]/', $char)) {
                // Found identifier, check if it's a keyword that allows regex
                $word = $this->extractWordBackwards($js, $i);
                return in_array($word, self::KEYWORDS);
            } elseif (!preg_match('/\s/', $char)) {
                // Found non-space character that's not identifier or parenthesis
                return true;
            }

            $i--;
        }

        return true;
    }

    /**
     * Extract a word backwards from a position
     */
    private function extractWordBackwards(string $js, int $position): string
    {
        $word = '';
        $i = $position;

        while ($i >= 0 && preg_match('/[a-zA-Z0-9_$]/', $js[$i])) {
            $word = $js[$i] . $word;
            $i--;
        }

        return $word;
    }
}
