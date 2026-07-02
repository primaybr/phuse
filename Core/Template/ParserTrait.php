<?php

declare(strict_types=1);

namespace Core\Template;

/**
 * ParserTrait - PHUSE Template Engine v1.2.7
 *
 * Syntax overview (familiar to Twig & Laravel Blade users):
 *
 *  Output variables          {{variable}}
 *  Nested / dot notation     {{user.profile.age}}
 *  Filters                   {{name|upper}}  |  {{name|substr:0:1|upper}}
 *  Raw / unescaped HTML      {!! htmlContent !!}
 *  Template comments         {# This comment is stripped #}
 *  Escape output tag         @{{variable}}  →  renders as literal {{variable}}
 *
 *  Conditionals              {% if condition %} … {% else %} … {% endif %}
 *  Foreach loops             {% foreach items as item %} … {% endforeach %}
 *  Numeric for loops         {% for i in 1..10 %} … {% endfor %}
 *
 * Single curly braces { } are no longer parsed as variable placeholders,
 * so inline CSS and inline JavaScript are 100% safe inside templates.
 */
trait ParserTrait
{

    // -----------------------------------------------------------------------
    // Core value helpers
    // -----------------------------------------------------------------------

    /**
     * Returns the replacement pair for a simple scalar value.
     * Uses {{key}} as the placeholder so single { } in CSS / JS are untouched.
     *
     * @param string $key  Variable name
     * @param string $val  Value to substitute
     * @return array  ['{{key}}' => 'value']
     */
    protected function parseValue(string $key, string $val): array
    {
        return ["{{".$key."}}" => $val];
    }

    /**
     * Resolves nested property access using dot notation (e.g. 'profile.age').
     *
     * @param mixed  $data The data array/object to traverse
     * @param string $path Dot-notation path (e.g. 'profile.age')
     * @return mixed|null  Resolved value or null if path doesn't exist
     */
    protected function resolveNestedProperty($data, string $path)
    {
        $keys    = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } elseif (is_object($current) && property_exists($current, $key)) {
                $current = $current->$key;
            } elseif (is_object($current) && method_exists($current, 'get'.ucfirst($key))) {
                $method  = 'get'.ucfirst($key);
                $current = $current->$method();
            } elseif (is_object($current) && $current instanceof \ArrayAccess && $current->offsetExists($key)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    // -----------------------------------------------------------------------
    // New v1.2.1 syntax helpers
    // -----------------------------------------------------------------------

    /**
     * Strips template comments  {# … #}  from the template.
     * Comments are removed entirely and do not appear in the output.
     *
     * Example:
     *   {# TODO: remove this later #}  →  (empty)
     *
     * @param string $template
     * @return string
     */
    protected function parseComments(string $template): string
    {
        return preg_replace('~\{#.*?#\}~s', '', $template);
    }

    /**
     * Processes raw / unescaped output tags  {!! variable !!}.
     * Use this only for trusted HTML content (e.g. rich-text stored in DB).
     *
     * Example:
     *   {!! body !!}  →  <p>Hello <strong>world</strong></p>
     *
     * @param string $template
     * @param array  $data
     * @return string
     */
    protected function parseRawOutput(string $template, array $data): string
    {
        return preg_replace_callback(
            '~\{!!\s*([a-zA-Z_][a-zA-Z0-9_.]*)\s*!!\}~',
            function (array $matches) use ($data): string {
                $value = $this->resolveNestedProperty($data, trim($matches[1]));
                if ($value === null || is_array($value) || is_object($value)) {
                    return $matches[0];
                }
                return (string) $value;
            },
            $template
        );
    }

    /**
     * Protects escaped output tags  @{{variable}}  so they are not parsed.
     * After all other parsing is done, they are restored as literal {{variable}}.
     * This mirrors Laravel Blade's @{{ }} escaping mechanism.
     *
     * Example:
     *   @{{variable}}  →  {{variable}}  (literal, not replaced)
     *
     * @param string $template
     * @param array  &$escapedBlocks  Storage for protected blocks
     * @return string
     */
    protected function parseEscapedSyntax(string $template, array &$escapedBlocks): string
    {
        return preg_replace_callback(
            '~@\{\{([^{}]*)\}\}~',
            function (array $matches) use (&$escapedBlocks): string {
                $placeholder              = '___ESCAPED_SYNTAX_'.count($escapedBlocks).'___';
                $escapedBlocks[$placeholder] = '{{'.$matches[1].'}}';
                return $placeholder;
            },
            $template
        );
    }

    /**
     * Restores escaped output blocks after parsing is complete.
     *
     * @param string $template
     * @param array  $escapedBlocks
     * @return string
     */
    protected function restoreEscapedSyntax(string $template, array $escapedBlocks): string
    {
        foreach ($escapedBlocks as $placeholder => $original) {
            $template = str_replace($placeholder, $original, $template);
        }
        return $template;
    }

    // -----------------------------------------------------------------------
    // Filter system
    // -----------------------------------------------------------------------

    /**
     * Processes filter expressions  {{variable|filter}}  or
     * {{variable|filter:param1:param2}}  or chained  {{var|f1|f2}}.
     *
     * @param string $template
     * @param array  $data
     * @return string
     */
    protected function parseFilters(string $template, array $data): string
    {
        // Matches {{variable|filter}} or {{variable|filter:param1:param2}}
        // including quoted parameters with spaces/commas: {{date|date:'M d, Y'}}
        $pattern = '~\{\{([a-zA-Z_][a-zA-Z0-9_.]*)\|([a-zA-Z_][a-zA-Z0-9_:|\'\" ,]+)\}\}~';

        return preg_replace_callback($pattern, function (array $matches) use ($data): string {
            $variablePath = $matches[1];
            $filterChain  = $matches[2];

            $value = $this->resolveNestedProperty($data, $variablePath);

            return $this->applyFilterChain($value, $filterChain);
        }, $template);
    }

    /**
     * Applies a chain of filters (pipe-separated) to a value.
     *
     * @param mixed  $value
     * @param string $filterChain  e.g. "substr:0:1|upper"
     * @return string
     */
    protected function applyFilterChain($value, string $filterChain): string
    {
        $filters = explode('|', $filterChain);
        $result  = $value;

        foreach ($filters as $filter) {
            $result = $this->applyFilterWithParams($result, $filter);
        }

        return (string) $result;
    }

    /**
     * Parses a single filter spec (name + colon-delimited params) and applies it.
     *
     * @param mixed  $value
     * @param string $filterSpec  e.g. "substr:0:1" or "date:'M d, Y'"
     * @return mixed
     */
    protected function applyFilterWithParams($value, string $filterSpec)
    {
        $parts      = preg_split('/(?<!\\\\):/', $filterSpec);
        $filterName = $parts[0];
        $params     = array_slice($parts, 1);

        $cleanParams = [];
        foreach ($params as $param) {
            $param = trim($param);
            if ((str_starts_with($param, "'") && str_ends_with($param, "'")) ||
                (str_starts_with($param, '"') && str_ends_with($param, '"'))) {
                $param = substr($param, 1, -1);
            }
            $cleanParams[] = $param;
        }

        return $this->applyFilter($value, $filterName, $cleanParams);
    }

    /**
     * Applies a named filter to a value.
     *
     * Available filters:
     *  substr, length, count, upper, lowercase, lower, lowercase,
     *  capitalize, trim, title, date, round, stars
     *
     * @param mixed  $value
     * @param string $filterName
     * @param array  $params
     * @return string
     */
    protected function applyFilter($value, string $filterName, array $params = []): string
    {
        switch ($filterName) {
            case 'substr':
                $start  = isset($params[0]) ? (int) $params[0] : 0;
                $length = isset($params[1]) ? (int) $params[1] : null;
                return $length !== null
                    ? substr((string) $value, $start, $length)
                    : substr((string) $value, $start);

            case 'length':
            case 'count':
                if (is_array($value) || $value instanceof \Countable) {
                    return (string) count($value);
                }
                return '0';

            case 'upper':
            case 'uppercase':
                return strtoupper((string) $value);

            case 'lower':
            case 'lowercase':
                return strtolower((string) $value);

            case 'capitalize':
                return ucwords(strtolower((string) $value));

            case 'trim':
                return trim((string) $value);

            case 'title':
                return ucwords(strtolower((string) $value));

            case 'date':
                $format    = isset($params[0]) ? trim($params[0], "'\"") : 'Y-m-d';
                if (is_numeric($value)) {
                    return date($format, (int) $value);
                } elseif (is_string($value)) {
                    $timestamp = strtotime($value);
                    if ($timestamp !== false) {
                        return date($format, $timestamp);
                    }
                }
                return (string) $value;

            case 'round':
                return (string) round((float) $value);

            case 'stars':
                $rating = round((float) $value);
                $filled = str_repeat('★', (int) $rating);
                $empty  = str_repeat('☆', 5 - (int) $rating);
                return $filled.$empty;

            default:
                return (string) $value;
        }
    }

    // -----------------------------------------------------------------------
    // Nested property / expression evaluation
    // -----------------------------------------------------------------------

    /**
     * Resolves  {{expression}}  placeholders (dot-notation, ternary, string
     * multiplication).  Filters are handled by parseFilters() first, so any
     * remaining {{…}} that contains a pipe is left untouched.
     *
     * Single { } are NOT matched, so CSS rules such as
     *   .btn { color: red; }
     * and JS objects such as
     *   var cfg = { debug: true };
     * pass through completely unchanged.
     *
     * @param string $template
     * @param array  $data
     * @return string
     */
    protected function parseNestedProperties(string $template, array $data): string
    {
        // Only match {{ … }} - single braces are ignored entirely
        $pattern = '~\{\{([^{}]+)\}\}~';

        return preg_replace_callback($pattern, function (array $matches) use ($data): string {
            $expression = trim($matches[1]);

            // Skip filter expressions - already processed by parseFilters()
            if (strpos($expression, '|') !== false) {
                return $matches[0];
            }

            // Ternary  condition ? 'yes' : 'no'
            if (strpos($expression, '?') !== false && strpos($expression, ':') !== false) {
                return $this->evaluateTernaryOperator($expression, $data);
            }

            // Python-style string multiplication  '★' * rating
            if (preg_match("/^'[^']*'\\s*\\*\\s*.+|.*\\*\\s*'[^']*'$/", $expression)) {
                return $this->evaluateStringMultiplication($expression, $data);
            }

            $value = $this->resolveNestedProperty($data, $expression);

            // Unresolved or non-scalar - leave the placeholder intact
            if ($value === null || is_array($value) || is_object($value)) {
                return $matches[0];
            }

            return (string) $value;
        }, $template);
    }

    /**
     * Evaluates a ternary expression  condition ? trueValue : falseValue .
     */
    protected function evaluateTernaryOperator(string $expression, array $data): string
    {
        $parts = preg_split('/(\?|\:)/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) !== 5) {
            return $expression;
        }

        $condition  = trim($parts[0]);
        $trueValue  = trim($parts[2]);
        $falseValue = trim($parts[4]);

        if ($this->evaluateSimpleCondition($condition, $data)) {
            return $this->resolveExpressionValue($trueValue, $data);
        }

        return $this->resolveExpressionValue($falseValue, $data);
    }

    /**
     * Evaluates Python-style string multiplication  '★' * count .
     */
    protected function evaluateStringMultiplication(string $expression, array $data): string
    {
        if (preg_match("/^'([^']*)'\\s*\\*\\s*(.+)$/", $expression, $matches)) {
            $char  = $matches[1];
            $count = $this->resolveExpressionValue(trim($matches[2]), $data);
        } elseif (preg_match("/^(.+)\\s*\\*\\s*'([^']*)'$/", $expression, $matches)) {
            $count = $this->resolveExpressionValue(trim($matches[1]), $data);
            $char  = $matches[2];
        } else {
            return $expression;
        }

        $count = (int) $count;
        return $count > 0 ? str_repeat($char, $count) : '';
    }

    /**
     * Evaluates a simple boolean condition for ternary operators.
     */
    protected function evaluateSimpleCondition(string $condition, array $data): bool
    {
        $condition = trim($condition);
        if ($condition === 'true')  return true;
        if ($condition === 'false') return false;

        $value = $this->resolveNestedProperty($data, $condition);
        return (bool) $value;
    }

    /**
     * Resolves an expression - either a quoted literal or a variable path.
     */
    protected function resolveExpressionValue(string $expression, array $data): string
    {
        $expression = trim($expression);

        if (preg_match("/^'([^']*)'$/", $expression, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^"([^"]*)"$/', $expression, $matches)) {
            return $matches[1];
        }

        $value = $this->resolveNestedProperty($data, $expression);
        if (is_array($value) || is_object($value)) {
            return '';
        }
        return (string) $value;
    }

    /**
     * Evaluates complex nested expressions (e.g. rating star strings).
     * Kept for advanced use-cases; called internally when needed.
     */
    protected function evaluateComplexExpression(string $expression, array $data): string
    {
        // Handle the specific case of rating stars:
        // {{'★' * (product.rating|round)}}{{'☆' * (5 - product.rating|round)}}
        if (preg_match(
            '/\{\{\'(.)\'\s*\*\s*\(([^|]+)\|round\)\}\}\{\{\'(.)\'\s*\*\s*\(5\s*-\s*([^|]+)\|round\)\}\}/',
            $expression,
            $matches
        )) {
            $filledChar = $matches[1];
            $ratingVar  = $matches[2];
            $emptyChar  = $matches[3];

            $rating      = $this->resolveNestedProperty($data, $ratingVar);
            $rating      = $this->applyFilter($rating, 'round', []);
            $rating      = (int) $rating;

            return str_repeat($filledChar, $rating).str_repeat($emptyChar, 5 - $rating);
        }

        // Generic: recursively process nested {{ }} blocks
        while (preg_match('/\{\{([^{}]*)\}\}/', $expression, $matches)) {
            $innerExpression = trim($matches[1]);
            $fullMatch       = $matches[0];

            if (preg_match("/^'([^']*)'\\s*\\*\\s*(.+)$/", $innerExpression, $subMatches)) {
                $char      = $subMatches[1];
                $countExpr = trim($subMatches[2]);

                if (strpos($countExpr, '|') !== false) {
                    [$var, $filter] = explode('|', $countExpr, 2);
                    $count = $this->resolveNestedProperty($data, trim($var));
                    $count = $this->applyFilter($count, trim($filter), []);
                } elseif (preg_match('/^(.+)\s*-\s*(.+)$/', $countExpr, $arithMatches)) {
                    $left  = $this->resolveExpressionValue(trim($arithMatches[1]), $data);
                    $right = $this->resolveExpressionValue(trim($arithMatches[2]), $data);
                    $count = (float) $left - (float) $right;
                } else {
                    $count = $this->resolveExpressionValue($countExpr, $data);
                }

                $replacement = str_repeat($char, (int) $count);
            } else {
                $replacement = $this->resolveExpressionValue($innerExpression, $data);
            }

            $expression = str_replace($fullMatch, $replacement, $expression);
        }

        return $expression;
    }

    // -----------------------------------------------------------------------
    // Array / loop parsing
    // -----------------------------------------------------------------------

    /**
     * Handles legacy block-style array loops:
     *   {{items}}  …row content with {{key}} …  {{/items}}
     *
     * This complements the modern {% foreach %} tag. It is kept for
     * backward compatibility and advanced use-cases.
     *
     * IMPORTANT: the old single-brace stripping hack has been removed.
     * Row content now uses {{key}} placeholders, so CSS / JS inside the
     * block are never corrupted.
     *
     * @param string       $template
     * @param string|array $var
     * @param string|array $data
     * @return array  [original_block => rendered_string]
     */
    protected function parseArray(string $template, string|array $var, string|array $data): array
    {
        $replace = [];

        // Block pattern: {{varname}} … content … {{/varname}}
        $pattern = '~\{\{\s*'.preg_quote((string) $var).'\s*\}\}(.+?)\{\{\s*/'.preg_quote((string) $var).'\s*\}\}~s';
        preg_match_all($pattern, $template, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $str = '';

            foreach ($data as $row) {
                $arr = [];

                foreach ($row as $key => $val) {
                    if (is_array($val)) {
                        $nested = $this->parseArray($key, $val, $match[1]);
                        if (!empty($nested)) {
                            $arr = array_merge($arr, $nested);
                        }
                        continue;
                    }
                    // Map {{key}} → value for each cell in the row
                    $arr['{{'.$key.'}}'] = is_array($val) ? implode(', ', $val) : (string) $val;
                }

                $str .= strtr($match[1], $arr);
            }

            // Store the rendered block - no brace-stripping, CSS/JS safe
            $replace[$match[0]] = $str;
        }

        return $replace;
    }

    /**
     * Main template parsing entry point.
     *
     * Processing pipeline:
     *  1. Protect @{{…}} escaped tags
     *  2. Strip {# … #} comments
     *  3. Process {!! raw !!} output
     *  4. Replace scalar & array placeholders via strtr
     *  5. Parse {% if %} / {% foreach %} / {% for %} control flow
     *  6. Parse {{var|filter}} filter expressions
     *  7. Parse {{nested.property}} dot-notation
     *  8. Restore escaped @{{}} tags as literal {{}}
     *
     * Note: HTML block protection has been removed. Since v1.2.1 uses double-brace
     * {{}} syntax, single { } in CSS and JavaScript are completely safe and no
     * longer require protection. Variables inside <style> and <script> blocks are
     * now processed naturally along with the rest of the template. Template syntax
     * literals inside <pre>/<code> examples must use HTML entities (&#123; &#125;)
     * or @{{}} escaping to prevent accidental substitution.
     *
     * @param string $template
     * @param array  $data
     * @return string
     */
    public function parseTemplate(string $template, array $data): string
    {
        $this->data = $data;

        // Step 1 - protect escaped syntax @{{…}}
        $escapedBlocks = [];
        $template = $this->parseEscapedSyntax($template, $escapedBlocks);

        // Step 2 - strip comments {# … #}
        $template = $this->parseComments($template);

        // Step 3 - raw output {!! var !!}
        $template = $this->parseRawOutput($template, $this->data);

        // Step 4 - build replacement map and apply strtr
        $replace = [];
        if ($data) {
            foreach ($data as $key => $val) {
                $parse   = is_array($val)
                    ? $this->parseArray($template, $key, $val)
                    : $this->parseValue($key, (string) $val);
                $replace = array_merge($replace, $parse);
            }
        }

        unset($data);
        $template = strtr($template, $replace);

        // Step 5 - control flow (foreach / if / for / while)
        $template = $this->parseConditionals($template);

        // Step 6 - filters  {{var|filter}}
        $template = $this->parseFilters($template, $this->data);

        // Step 7 - nested properties  {{user.profile.age}}
        $template = $this->parseNestedProperties($template, $this->data);

        // Step 8 - restore escaped @{{}} → {{}}
        $template = $this->restoreEscapedSyntax($template, $escapedBlocks);

        return $template;
    }

    // -----------------------------------------------------------------------
    // Conditional parsing
    // -----------------------------------------------------------------------

    /**
     * Entry point for all control-flow parsing.
     *
     * @param string $template
     * @return string
     */
    protected function parseConditionals(string $template): string
    {
        // foreach loops (nesting-aware)
        $template = $this->parseNestedForeach($template);

        // if / elseif / else / endif (nesting-aware)
        $template = $this->parseNestedIfBlocks($template);

        // {% for var in start..end %}
        $forPattern = '~{%\s*for\s+(\w+)\s+in\s+(\d+)\s*\.\.\s*(\d+)\s*%}(.*?){%\s*endfor\s*%}~s';
        $template   = preg_replace_callback($forPattern, function (array $matches): string {
            return $this->parseFor($matches);
        }, $template);

        // {% while condition %}
        $whilePattern = '~{%\s*while\s+([^%]*?)\s*%}(.*?){%\s*endwhile\s*%}~s';
        $template     = preg_replace_callback($whilePattern, function (array $matches): string {
            return $this->parseWhile($matches);
        }, $template);

        return $template;
    }

    /**
     * Parses if/else/elseif/endif blocks with proper nesting support.
     */
    protected function parseNestedIfBlocks(string $template): string
    {
        $blocks = $this->findTopLevelIfBlocks($template);

        foreach ($blocks as $block) {
            $parsedContent = $this->parseIfBlock([
                'condition'    => $block['condition'],
                'if_content'   => $block['if_content'],
                'else_content' => $block['else_content'],
            ]);

            $template = str_replace($block['full_match'], $parsedContent, $template);
        }

        return $template;
    }

    /**
     * Finds the position of a top-level {% else %} within a full if-block string.
     * Skips any {% else %} that belongs to a nested {% if %}...{% endif %} pair.
     *
     * @param string $blockContent  The full block including opening {% if %} and closing {% endif %}.
     * @return int|false  Position of the top-level {% else %}, or false if none exists.
     */
    protected function findTopLevelElse(string $blockContent): int|false
    {
        // Start scanning after the opening {% if ... %} tag
        $startPos = (int) strpos($blockContent, '%}') + 2;
        $depth    = 0;
        $i        = $startPos;
        $length   = strlen($blockContent);

        while ($i < $length) {
            $nextIf    = strpos($blockContent, '{% if',    $i);
            $nextEndif = strpos($blockContent, '{% endif', $i);
            $nextElse  = strpos($blockContent, '{% else %}', $i);

            $candidates = [];
            if ($nextIf    !== false) $candidates[] = ['pos' => $nextIf,    'type' => 'if'];
            if ($nextEndif !== false) $candidates[] = ['pos' => $nextEndif, 'type' => 'endif'];
            if ($nextElse  !== false) $candidates[] = ['pos' => $nextElse,  'type' => 'else'];

            if (empty($candidates)) break;

            usort($candidates, static fn ($a, $b) => $a['pos'] - $b['pos']);
            $first = $candidates[0];

            if ($first['type'] === 'if') {
                $depth++;
                $i = $first['pos'] + 7;
            } elseif ($first['type'] === 'endif') {
                if ($depth === 0) {
                    break; // reached the outer {% endif %}, no top-level else found
                }
                $depth--;
                $i = $first['pos'] + 11;
            } else { // else
                if ($depth === 0) {
                    return $first['pos'];
                }
                $i = $first['pos'] + 10; // {% else %} is 10 chars
            }
        }

        return false;
    }

    /**
     * Finds top-level {% if %} blocks (ignoring nested ones).
     */
    protected function findTopLevelIfBlocks(string $template): array
    {
        $blocks = [];
        $length = strlen($template);
        $i      = 0;

        while ($i < $length) {
            $ifPos = strpos($template, '{% if', $i);
            if ($ifPos === false) {
                break;
            }

            $depth  = 0;
            $j      = $ifPos + 7;
            $endPos = false;

            while ($j < $length) {
                $nextIf    = strpos($template, '{% if',    $j);
                $nextEndif = strpos($template, '{% endif', $j);

                if ($nextEndif === false) {
                    break;
                }

                if ($nextIf !== false && $nextIf < $nextEndif) {
                    $depth++;
                    $j = $nextIf + 7;
                } else {
                    if ($depth === 0) {
                        $endPos = $nextEndif;
                        break;
                    } else {
                        $depth--;
                        $j = $nextEndif + 11;
                    }
                }
            }

            if ($endPos !== false) {
                $blockContent = substr($template, $ifPos, $endPos - $ifPos + 11);

                $elsePos = $this->findTopLevelElse($blockContent);

                if ($elsePos !== false) {
                    $ifLine = substr($blockContent, 0, $elsePos);

                    if (preg_match('/{%\s*if\s+([^%]*?)\s*%}/', $ifLine, $conditionMatch)) {
                        $condition = trim($conditionMatch[1]);
                    } else {
                        $condition = '';
                    }

                    $ifContent   = substr($ifLine, strlen($conditionMatch[0]));
                    $elseStart   = $elsePos + 9;
                    // The outer {% endif %} is always the last 11 chars of $blockContent
                    $elseEnd     = strlen($blockContent) - 11;
                    $elseContent = $elseEnd > $elseStart
                        ? substr($blockContent, $elseStart, $elseEnd - $elseStart)
                        : '';

                    $ifContent   = trim($ifContent);
                    $elseContent = trim($elseContent);

                    if (substr($elseContent, 0, 1) === '}') {
                        $elseContent = trim(substr($elseContent, 1));
                    }
                } else {
                    if (preg_match('/^{%[^%]*%}/', $blockContent, $matches)) {
                        $ifLine = $matches[0];
                    } else {
                        $ifLine = substr($blockContent, 0, strpos($blockContent, "%}") + 2);
                    }

                    if (preg_match('/{%\s*if\s+([^%]*?)\s*%}/', $ifLine, $conditionMatch)) {
                        $condition = trim($conditionMatch[1]);
                    } else {
                        $condition = '';
                    }

                    // Extract content from $blockContent (not $ifLine which is just the opening tag)
                    // Find where the opening tag ends and where {% endif %} begins
                    $openTagLen = strlen($conditionMatch[0]);
                    $endifPos   = strrpos($blockContent, '{% endif');
                    $ifContent  = $endifPos !== false
                        ? trim(substr($blockContent, $openTagLen, $endifPos - $openTagLen))
                        : '';
                    $elseContent = '';
                }

                $blocks[] = [
                    'full_match'   => $blockContent,
                    'condition'    => $condition,
                    'if_content'   => $ifContent,
                    'else_content' => $elseContent,
                ];

                $i = $endPos + 11;
            } else {
                $i = $ifPos + 7;
            }
        }

        return $blocks;
    }

    /**
     * Evaluates a single if block and returns the appropriate branch content.
     */
    protected function parseIfBlock(array $block): string
    {
        return $this->evaluateCondition($block['condition'])
            ? $block['if_content']
            : $block['else_content'];
    }

    // -----------------------------------------------------------------------
    // Foreach loop parsing
    // -----------------------------------------------------------------------

    /**
     * Processes a single {% foreach iterable as loopVar %} block.
     * Supports:
     *  - Simple scalar loop items:  {{loopVar}}
     *  - Nested property access:    {{loopVar.name}}, {{loopVar.profile.age}}
     *  - Filters inside loops:      {{loopVar.name|upper}}
     *  - Nested {% foreach %} blocks
     *  - {% if %} / {% else %} / {% endif %} inside loops
     *
     * @param array $match  [full_match, iterable_var, loop_var, content]
     * @return string
     */
    protected function parseForeach(array $match): string
    {
        $iterableVar = $match[1];
        $loopVar     = $match[2];
        $content     = $match[3];

        $iterable = $this->resolveNestedProperty($this->data, $iterableVar);

        if (!is_iterable($iterable)) {
            return '';
        }

        $result = '';

        foreach ($iterable as $value) {
            $replacements = [];

            // Simple scalar replacement  {{loopVar}}
            $replacements['{{'.$loopVar.'}}'] = is_array($value) ? json_encode($value) : (string) $value;

            // Nested property access  {{loopVar.name}}, {{loopVar.profile.age}}
            if (is_array($value) || is_object($value)) {
                $flattened = $this->flattenArray($value, $loopVar);
                foreach ($flattened as $nestedKey => $nestedValue) {
                    $replacements['{{'.$nestedKey.'}}'] = (string) $nestedValue;
                }
            }

            $processedContent = strtr($content, $replacements);

            // Apply filters with current loop data context
            $loopData         = array_merge($this->data, [$loopVar => $value]);

            // Process {!! raw !!} output inside loop iterations
            $processedContent = $this->parseRawOutput($processedContent, $loopData);

            $processedContent = $this->parseFilters($processedContent, $loopData);

            // Process nested foreach loops
            $processedContent = $this->processNestedForeach($processedContent, $value, $loopVar);

            // Process if/else inside loop
            $processedContent = $this->parseConditionalsInLoop($processedContent, $value, $loopVar);

            $result .= $processedContent;
        }

        return $result;
    }

    /**
     * Processes nested {% foreach parentVar.property as childVar %} blocks.
     */
    protected function processNestedForeach(string $content, $parentValue, string $parentVar): string
    {
        $pattern = '~{%\s*foreach\s+([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\s+as\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*%}(.*?){%\s*endforeach\s*%}~s';

        $content = preg_replace_callback($pattern, function (array $matches) use ($parentValue, $parentVar): string {
            $varName        = $matches[1];
            $property       = $matches[2];
            $loopVar        = $matches[3];
            $nestedContent  = $matches[4];

            if ($varName !== $parentVar) {
                return $matches[0];
            }

            $nestedIterable = null;
            if (is_array($parentValue) && isset($parentValue[$property])) {
                $nestedIterable = $parentValue[$property];
            } elseif (is_object($parentValue) && property_exists($parentValue, $property)) {
                $nestedIterable = $parentValue->$property;
            } elseif (is_object($parentValue) && method_exists($parentValue, 'get'.ucfirst($property))) {
                $method         = 'get'.ucfirst($property);
                $nestedIterable = $parentValue->$method();
            }

            if (!is_iterable($nestedIterable)) {
                return '';
            }

            $nestedResult = '';
            foreach ($nestedIterable as $item) {
                $itemReplacements = [];
                $itemReplacements['{{'.$loopVar.'}}'] = is_array($item) ? json_encode($item) : (string) $item;

                if (is_array($item) || is_object($item)) {
                    $flattened = $this->flattenArray($item, $loopVar);
                    foreach ($flattened as $nestedKey => $nestedValue) {
                        $itemReplacements['{{'.$nestedKey.'}}'] = (string) $nestedValue;
                    }
                }

                $nestedResult .= strtr($nestedContent, $itemReplacements);
            }

            return $nestedResult;
        }, $content);

        return $content;
    }

    /**
     * Flattens a nested array/object into dot-notation keys.
     * e.g. ['profile' => ['age' => 30]]  →  ['prefix.profile.age' => 30]
     */
    protected function flattenArray($data, string $prefix = '', array &$result = []): array
    {
        if (!is_array($data) && !is_object($data)) {
            return $result;
        }

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) || is_object($value)) {
                $this->flattenArray($value, $fullKey, $result);
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Finds and processes all top-level {% foreach %} blocks.
     */
    protected function parseNestedForeach(string $template): string
    {
        $blocks = $this->findTopLevelForeachBlocks($template);

        foreach ($blocks as $block) {
            $parsedContent = $this->parseForeach([
                $block['full_match'],
                $block['iterable_var'],
                $block['loop_var'],
                $block['content'],
            ]);

            $template = str_replace($block['full_match'], $parsedContent, $template);
        }

        return $template;
    }

    /**
     * Finds top-level {% foreach %} blocks (not nested inside other foreach).
     */
    protected function findTopLevelForeachBlocks(string $template): array
    {
        $blocks = [];
        $length = strlen($template);
        $i      = 0;

        while ($i < $length) {
            $foreachPos = strpos($template, '{% foreach', $i);
            if ($foreachPos === false) {
                break;
            }

            $depth  = 0;
            $j      = $foreachPos + 11;
            $endPos = false;

            while ($j < $length) {
                $nextForeach    = strpos($template, '{% foreach',    $j);
                $nextEndForeach = strpos($template, '{% endforeach', $j);

                if ($nextEndForeach === false) {
                    break;
                }

                if ($nextForeach !== false && $nextForeach < $nextEndForeach) {
                    $depth++;
                    $j = $nextForeach + 11;
                } else {
                    if ($depth === 0) {
                        $endPos = $nextEndForeach;
                        break;
                    } else {
                        $depth--;
                        $j = $nextEndForeach + 16;
                    }
                }
            }

            if ($endPos !== false) {
                $blockContent  = substr($template, $foreachPos, $endPos - $foreachPos + 16);
                $foreachEndPos = strpos($blockContent, '%}') + 2;
                $foreachLine   = substr($blockContent, 0, $foreachEndPos);
                $content       = substr($blockContent, $foreachEndPos, strlen($blockContent) - $foreachEndPos - 16);

                if (preg_match('~{%\s*foreach\s+(\w+|\w+\.\w+)\s+as\s+(\w+)\s*%}~', $foreachLine, $matches)) {
                    $blocks[] = [
                        'full_match'   => $blockContent,
                        'iterable_var' => $matches[1],
                        'loop_var'     => $matches[2],
                        'content'      => $content,
                    ];
                }

                $i = $endPos + 16;
            } else {
                $i = $foreachPos + 11;
            }
        }

        return $blocks;
    }

    /**
     * Finds ALL foreach blocks with depth metadata (used internally).
     */
    protected function findForeachBlocks(string $template): array
    {
        $blocks = [];
        $length = strlen($template);
        $i      = 0;

        while ($i < $length) {
            $startPos = strpos($template, '{% foreach', $i);
            if ($startPos === false) break;

            $depth  = 0;
            $j      = $startPos + 11;
            $endPos = false;

            while ($j < $length) {
                $nextForeach    = strpos($template, '{% foreach',    $j);
                $nextEndForeach = strpos($template, '{% endforeach', $j);

                if ($nextEndForeach === false) break;

                if ($nextForeach !== false && $nextForeach < $nextEndForeach) {
                    $depth++;
                    $j = $nextForeach + 11;
                } else {
                    if ($depth === 0) {
                        $endPos = $nextEndForeach;
                        break;
                    } else {
                        $depth--;
                        $j = $nextEndForeach + 16;
                    }
                }
            }

            if ($endPos !== false) {
                $blockContent = substr($template, $startPos, $endPos - $startPos + 16);

                if (preg_match('~{%\s*foreach\s+(\w+|\w+\.\w+)\s+as\s+(\w+)\s*%}(.*?){%\s*endforeach\s*%}~s', $blockContent, $matches)) {
                    $blocks[] = [
                        'full_match'   => $blockContent,
                        'iterable_var' => $matches[1],
                        'loop_var'     => $matches[2],
                        'content'      => $matches[3],
                        'depth'        => 0,
                    ];
                }

                $i = $endPos + 16;
            } else {
                $i = $startPos + 11;
            }
        }

        foreach ($blocks as &$block) {
            $block['depth'] = 0;
            foreach ($blocks as $otherBlock) {
                if ($block['full_match'] !== $otherBlock['full_match'] &&
                    strpos($otherBlock['full_match'], $block['full_match']) !== false) {
                    $block['depth']++;
                }
            }
        }

        return $blocks;
    }

    /**
     * Parses {% if %} / {% else %} / {% endif %} blocks inside a loop iteration.
     */
    protected function parseConditionalsInLoop(string $content, $loopValue, string $loopVar): string
    {
        $ifPattern = '~\{%\s*if\s+([^%]*?)\s*%\}([^{]*?)(?:\{%\s*else\s*%\}([^{]*?))?\{%\s*endif\s*%\}~s';

        return preg_replace_callback($ifPattern, function (array $matches) use ($loopValue, $loopVar): string {
            $condition   = trim($matches[1]);
            $ifContent   = trim($matches[2]);
            $elseContent = isset($matches[3]) ? trim($matches[3]) : '';

            return $this->evaluateConditionInLoop($condition, $loopValue, $loopVar)
                ? $ifContent
                : $elseContent;
        }, $content);
    }

    /**
     * Evaluates a condition with access to the current loop variable.
     */
    protected function evaluateConditionInLoop(string $condition, $loopValue, string $loopVar): bool
    {
        $condition = preg_replace('/\bnot\s+/', '!', $condition);
        $parts     = preg_split('/(\'[^\']*\'|"[^"]*")/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $idx => &$part) {
            if ($idx % 2 === 0) {
                if (preg_match_all('~\$?([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)~', $part, $matches)) {
                    $variables = $matches[0];
                    $varPaths  = $matches[1];

                    for ($k = count($varPaths) - 1; $k >= 0; $k--) {
                        $varMatch = $variables[$k];
                        $varPath  = $varPaths[$k];

                        if (strpos($varPath, $loopVar.'.') === 0 || $varPath === $loopVar) {
                            $value = $this->resolveNestedProperty([$loopVar => $loopValue], $varPath);
                        } else {
                            $value = $this->resolveNestedProperty($this->data, $varPath);
                        }

                        if (is_bool($value)) {
                            $replacement = $value ? 'true' : 'false';
                        } elseif (is_string($value)) {
                            $replacement = "'".addslashes($value)."'";
                        } elseif (is_numeric($value)) {
                            $replacement = (string) $value;
                        } elseif (is_null($value)) {
                            $replacement = 'null';
                        } else {
                            $replacement = 'false';
                        }

                        $part = str_replace($varMatch, $replacement, $part);
                    }
                }
            }
        }

        $condition = implode('', $parts);

        try {
            $result = eval("return ($condition);");
            return (bool) $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // -----------------------------------------------------------------------
    // Legacy loop helpers (kept for compatibility)
    // -----------------------------------------------------------------------

    /** @deprecated Use {% foreach %} instead */
    protected function parseElse(array $match): string
    {
        return $match[3].$match[0];
    }

    /** @deprecated Use {% foreach %} instead */
    protected function parseElseif(array $match): string
    {
        $condition = $match[1];
        $content   = $match[2];
        $end       = $match[3] ?? '';

        if ($this->evaluateCondition($condition)) {
            return $content.'{% endif %}';
        }

        return $end;
    }

    // -----------------------------------------------------------------------
    // For / While loops
    // -----------------------------------------------------------------------

    /**
     * Processes  {% for loopVar in start..end %}  numeric range loops.
     * Inside the loop body use  {{loopVar}}  to output the current value.
     *
     * @param array $match
     * @return string
     */
    protected function parseFor(array $match): string
    {
        $loopVar = $match[1];
        $start   = (int) $match[2];
        $end     = (int) $match[3];
        $content = $match[4];

        $result = '';
        for ($i = $start; $i <= $end; $i++) {
            $result .= str_replace('{{'.$loopVar.'}}', (string) $i, $content);
        }

        return $result;
    }

    /**
     * Processes  {% while condition %}  loops.
     */
    protected function parseWhile(array $match): string
    {
        $condition = $match[1];
        $content   = $match[2];
        $result    = '';

        while ($this->evaluateCondition($condition)) {
            $result .= $content;
        }

        return $result;
    }

    // -----------------------------------------------------------------------
    // Condition evaluation
    // -----------------------------------------------------------------------

    /**
     * Evaluates a condition string as a PHP boolean expression.
     * Supports: variable paths, comparison operators, the "not" keyword,
     * filter expressions (e.g. items|count > 0), and quoted strings.
     *
     * @param string $condition
     * @return bool
     */
    protected function evaluateCondition(string $condition): bool
    {
        $condition = preg_replace('/\bnot\s+/', '!', $condition);
        $parts     = preg_split('/(\'[^\']*\'|"[^"]*")/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $idx => &$part) {
            if ($idx % 2 === 0) {
                $part = preg_replace_callback('~\$?([a-zA-Z_][a-zA-Z0-9_.|\'\":]*)~', function (array $match): string {
                    $expression = $match[1];

                    if (strpos($expression, '|') !== false) {
                        if (strpos($expression, '|count') !== false) {
                            $varPath = str_replace('|count', '', $expression);
                            $value   = $this->resolveNestedProperty($this->data, trim($varPath));
                            $value   = (is_array($value) || $value instanceof \Countable)
                                ? count($value)
                                : 0;
                        } else {
                            // Wrap in {{}} so parseFilters can process it
                            $result = $this->parseFilters('{{'.$expression.'}}', $this->data);
                            // If parseFilters matched, result is the value; otherwise unchanged
                            $value  = ($result !== '{{'.$expression.'}}') ? $result : null;
                        }
                    } else {
                        $value = $this->resolveNestedProperty($this->data, $expression);
                    }

                    if (is_bool($value))          return $value ? 'true' : 'false';
                    if (is_string($value))         return "'".addslashes($value)."'";
                    if (is_numeric($value))        return (string) $value;
                    if (is_null($value))           return 'null';
                    if (is_array($value))          return !empty($value) ? 'true' : 'false';
                    if (is_object($value))         return (bool) $value ? 'true' : 'false';
                    return 'false';
                }, $part);
            }
        }

        $condition = implode('', $parts);

        try {
            $result = eval("return ($condition);");
            return (bool) $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // -----------------------------------------------------------------------
    // HTML block protection
    // -----------------------------------------------------------------------

    /**
     * Pre-processes script src attributes so dynamic URLs inside them
     * (e.g. src="{{assetsUrl}}app.js") are resolved correctly.
     */
    protected function preProcessAttributes(string $template, array &$processedBlocks): string
    {
        return preg_replace_callback(
            '~<script([^>]*)src="([^"]*)"([^>]*)>~is',
            function (array $matches) use (&$processedBlocks): string {
                $srcPlaceholder              = '___PROCESSED_SCRIPT_SRC_'.count($processedBlocks).'___';
                $processedBlocks[$srcPlaceholder] = $matches[2];
                return '<script'.$matches[1].'src="'.$srcPlaceholder.'"'.$matches[3].'>';
            },
            $template
        );
    }

    /**
     * Protects raw HTML blocks (<style>, <script>, <code>, <pre>) from
     * template parsing.  This prevents CSS rules and JavaScript code from
     * being accidentally mutated.
     *
     * With the new {{}} syntax this is largely academic (since single { }
     * are no longer parsed), but it is still useful to prevent the filter /
     * nested-property regexes from touching minified/uglified code.
     */
    protected function protectHtmlBlocks(string $template, array &$protectedBlocks): string
    {
        foreach (['style', 'code', 'pre'] as $tag) {
            $pattern  = '~<'.$tag.'[^>]*>(.*?)</'.$tag.'>~is';
            $template = preg_replace_callback($pattern, function (array $matches) use (&$protectedBlocks, $tag): string {
                $placeholder              = '___PROTECTED_'.strtoupper($tag).'_'.count($protectedBlocks).'___';
                $protectedBlocks[$placeholder] = $matches[0];
                return $placeholder;
            }, $template);
        }

        // Script blocks (except those with pre-processed src placeholders)
        $scriptPattern = '~<script(?!\s[^>]*___PROCESSED_SCRIPT_SRC_)[^>]*>(.*?)</script>~is';
        $template      = preg_replace_callback($scriptPattern, function (array $matches) use (&$protectedBlocks): string {
            $placeholder              = '___PROTECTED_SCRIPT_'.count($protectedBlocks).'___';
            $protectedBlocks[$placeholder] = $matches[0];
            return $placeholder;
        }, $template);

        return $template;
    }

    /**
     * Restores pre-processed script src attributes and resolves any
     * {{variable}} placeholders they contain.
     */
    protected function restorePreProcessedAttributes(string $template, array $processedBlocks): string
    {
        foreach ($processedBlocks as $placeholder => $originalContent) {
            $processedContent = $this->parseNestedProperties($originalContent, $this->data);
            $template         = str_replace($placeholder, $processedContent, $template);
        }

        return $template;
    }

    /**
     * Restores protected HTML blocks.
     * Script blocks also have their {{variable}} placeholders resolved,
     * so you can still inject dynamic values into <script> tags using
     * the double-brace syntax.
     *
     * Example (in a <script> block):
     *   const apiUrl = "{{apiUrl}}";
     */
    protected function restoreHtmlBlocks(string $template, array $protectedBlocks): string
    {
        // Build a replacement map for scalar values using the new {{key}} syntax
        $replace = [];
        foreach ($this->data as $key => $val) {
            if (!is_array($val)) {
                $replace['{{'.$key.'}}'] = (string) $val;
            }
        }

        foreach ($protectedBlocks as $placeholder => $originalContent) {
            // Substitute {{variables}} in both <script> and <style> blocks
            if (!empty($replace) && (
                str_starts_with($placeholder, '___PROTECTED_SCRIPT_') ||
                str_starts_with($placeholder, '___PROTECTED_STYLE_')
            )) {
                $originalContent = strtr($originalContent, $replace);
            }
            $template = str_replace($placeholder, $originalContent, $template);
        }

        return $template;
    }
}
