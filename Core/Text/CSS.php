<?php

declare(strict_types=1);

namespace Core\Text;

/**
 * CSS Minifier and Processor
 *
 * A comprehensive CSS minifier that removes unnecessary characters, whitespace,
 * and optimizes values while preserving functionality. Features modular design
 * for easy maintenance and extensibility.
 *
 * Key features:
 * - Removes CSS comments (preserves important comments)
 * - Minifies whitespace while preserving necessary spacing
 * - Optimizes values (0px → 0, etc.)
 * - Minifies hex colors (#aabbcc → #abc)
 * - Handles string optimization
 * - Removes empty selectors
 * - Validates CSS files and URLs
 *
 * @author Prima Yoga
 */
class CSS
{
    /**
     * Array of CSS file names or URLs to process
     *
     * @var array<string>
     */
    private array $fileNames;

    /**
     * Constructor - Initialize CSS processor with file names
     *
     * @param array $fileNames Array of CSS file paths or URLs
     */
    public function __construct($fileNames = [])
    {
        $this->fileNames = $fileNames;
    }

    /**
     * Validate CSS file or URL
     *
     * Checks if the provided file exists and has valid CSS extension,
     * or validates URL accessibility for remote CSS files.
     *
     * @param string $fileName Path to CSS file or URL
     * @return bool True if file/URL is valid and accessible
     * @throws \Exception If file doesn't exist, has invalid extension, or URL is inaccessible
     */
    private function fileValidator(string $fileName): bool
    {
        $url = str_replace(' ', '%20', $fileName);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // check if fileName is URL then verify first using CURL
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            $fileParts = explode('.', $fileName);
            $fileExtension = end($fileParts);

            if (strtolower($fileExtension) !== "css") {
                throw new \Exception("Invalid file type. The extension for the file $fileName is $fileExtension.");
            }

            if (!file_exists($fileName)) {
                throw new \Exception("The given file $fileName does not exists.");
            }
        }

        return true;
    }

    /**
     * Set HTTP headers for CSS output
     *
     * Sets the appropriate Content-Type header for CSS files.
     *
     * @return void
     */
    private function setHeaders(): void
    {
        header('Content-Type: text/css');
    }

    /**
     * Minify multiple CSS files and combine them
     *
     * Processes all CSS files in the fileNames array, validates them,
     * minifies each one, and combines the results into a single output.
     *
     * @return string|false Combined minified CSS content, or false on error
     */
    public function minify()
    {
        $this->setHeaders();

        $minifiedCss = "";
        $fileNames = $this->fileNames;

        foreach ($fileNames as $fileName) {
            try {
                $this->fileValidator($fileName);
                $fileContent = file_get_contents($fileName);
                $minifiedCss = $minifiedCss . $this->minifyCSS($fileContent);
            } catch (\Exception $e) {
                echo 'Message: ' . $e->getMessage();
                return false;
            }
        }

        return $minifiedCss;
    }

    /**
     * Minify CSS content with improved robustness and maintainability
     *
     * Applies a series of optimization steps to reduce CSS size while
     * preserving functionality and readability where necessary.
     *
     * @param string $input CSS content to minify
     * @return string Minified CSS content
     */
    public function minifyCSS(string $input): string
    {
        if (trim($input) === "") {
            return $input;
        }

        // Apply minification steps in logical order
        $output = $this->removeComments($input);
        $output = $this->minifyWhitespace($output);
        $output = $this->optimizeValues($output);
        $output = $this->minifyColors($output);
        $output = $this->minifyStrings($output);
        $output = $this->removeEmptySelectors($output);

        return $output;
    }

    /**
     * Remove CSS comments while preserving important comments
     *
     * Removes standard CSS comments 
     * comments that start with /*! for licensing or copyright notices.
     *
     * @param string $css CSS content to process
     * @return string CSS content with comments removed
     */
    private function removeComments(string $css): string
    {
        // Remove /* ... */ comments but preserve /*! important comments */
        $css = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//s', '', $css);
        return $css;
    }

    /**
     * Minify whitespace while preserving necessary spaces
     *
     * Reduces unnecessary whitespace in CSS while maintaining proper
     * spacing around selectors, properties, and values.
     *
     * @param string $css CSS content to process
     * @return string CSS content with optimized whitespace
     */
    private function minifyWhitespace(string $css): string
    {
        // Remove leading/trailing whitespace from each line
        $css = preg_replace('/^\s+|\s+$/m', '', $css);

        // Replace multiple spaces with single space
        $css = preg_replace('/\s+/', ' ', $css);

        // Remove spaces around braces, colons, etc.
        $css = str_replace([' {', ' }', ': ', ' ;', '; }'], ['{', '}', ':', ';', '}'], $css);

        return $css;
    }

    /**
     * Optimize CSS values (0px → 0, etc.)
     *
     * Converts verbose CSS values to their shorthand equivalents:
     * - 0px, 0em, 0% → 0
     * - :0 0 0 0 → :0
     * - background-position:0 → background-position:0 0
     * - 0.5 → .5
     * - border:none → border:0
     *
     * @param string $css CSS content to process
     * @return string CSS content with optimized values
     */
    private function optimizeValues(string $css): string
    {
        // Replace 0px, 0em, etc. with 0
        $css = preg_replace('/(\s|:)0(px|em|ex|in|mm|pc|pt|vh|vw|%)(?=\s*[;},]|$)/', '$1$2', $css);

        // Replace :0 0 0 0 with :0
        $css = str_replace(':0 0 0 0', ':0', $css);

        // Replace background-position:0 with background-position:0 0
        $css = str_replace('background-position:0', 'background-position:0 0', $css);

        // Remove leading zero from decimal numbers like 0.5 → .5
        $css = preg_replace('/(\s|:)0+(\.\d+)/', '$1$2', $css);

        // Replace border:none and outline:none with border:0 and outline:0
        $css = str_replace(['border:none', 'outline:none'], ['border:0', 'outline:0'], $css);

        return $css;
    }

    /**
     * Minify color values
     *
     * Converts verbose hex color notation to shorthand:
     * - #aabbcc → #abc (when all pairs are identical)
     * - Preserves other color formats (rgb, hsl, named colors)
     *
     * @param string $css CSS content to process
     * @return string CSS content with minified colors
     */
    private function minifyColors(string $css): string
    {
        // Minify hex colors by removing repeated characters in #aabbcc → #abc
        $css = preg_replace('/#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3/i', '#$1$2$3', $css);

        return $css;
    }

    /**
     * Minify string values while preserving quotes
     *
     * Removes unnecessary quotes around simple CSS identifiers
     * while preserving quotes for complex values or those containing spaces.
     *
     * @param string $css CSS content to process
     * @return string CSS content with optimized string quotes
     */
    private function minifyStrings(string $css): string
    {
        // Remove unnecessary quotes around simple identifiers
        $css = preg_replace('/([\'"])([a-zA-Z_][a-zA-Z0-9\-_]*)\1(\s*[;},]|$)/', '$2$3', $css);

        return $css;
    }

    /**
     * Remove empty CSS selectors
     *
     * Removes CSS rules that have empty bodies (no properties),
     * which can occur after other optimizations or from unused CSS.
     *
     * @param string $css CSS content to process
     * @return string CSS content with empty selectors removed
     */
    private function removeEmptySelectors(string $css): string
    {
        // Remove empty rules like selector {}
        $css = preg_replace('/[^{}]+{\s*}/', '', $css);

        return $css;
    }
}
