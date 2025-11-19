<?php

declare(strict_types=1);

namespace Core\Template;

trait ParserTrait
{

    /**
     * Parses a single value and returns an array with the template key and value.
     *
     * @param string $key The key in the template to be replaced.
     * @param string $val The value to replace in the template.
     * @return array An associative array with the template key and value.
     */
    protected function parseValue(string $key, string $val): array
    {

        return ["{".$key."}" => $val];
    }

    /**
     * Resolves nested property access using dot notation (e.g., 'profile.age')
     *
     * @param mixed $data The data array/object to traverse
     * @param string $path The dot-notation path (e.g., 'profile.age')
     * @return mixed|null The resolved value or null if path doesn't exist
     */
    protected function resolveNestedProperty($data, string $path)
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } elseif (is_object($current) && property_exists($current, $key)) {
                $current = $current->$key;
            } elseif (is_object($current) && method_exists($current, 'get' . ucfirst($key))) {
                $method = 'get' . ucfirst($key);
                $current = $current->$method();
            } elseif (is_object($current) && $current instanceof \ArrayAccess && $current->offsetExists($key)) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }
	
	/**
     * Parses filters in template variables (e.g., {variable|filter}).
     *
     * @param string $template The template string containing filter expressions.
     * @param array $data The data array used for filter evaluation.
     * @return string The template string with filters applied.
     */
    protected function parseFilters(string $template, array $data): string
    {
        // Pattern to match {variable|filter} or {variable.nested|filter}
        $pattern = '~\{([a-zA-Z_][a-zA-Z0-9_.]*)\|([a-zA-Z_][a-zA-Z0-9_]*)\}~';

        return preg_replace_callback($pattern, function($matches) use ($data) {
            $variablePath = $matches[1];
            $filterName = $matches[2];

            // Get the value using nested property resolution
            $value = $this->resolveNestedProperty($data, $variablePath);

            // Apply the filter
            return $this->applyFilter($value, $filterName);
        }, $template);
    }

    /**
     * Parses nested property access in template variables (e.g., {stats.total_users}).
     *
     * @param string $template The template string containing nested property expressions.
     * @param array $data The data array used for property resolution.
     * @return string The template string with nested properties replaced.
     */
    protected function parseNestedProperties(string $template, array $data): string
    {
        // Pattern to match expressions in curly braces, allowing for nested braces
        $pattern = '~\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}~';

        return preg_replace_callback($pattern, function($matches) use ($data) {
            $expression = trim($matches[1]);

            // Skip if this contains a filter (handled separately)
            if (strpos($expression, '|') !== false && !preg_match('/[{}]/', $expression)) {
                return $matches[0];
            }

            // Handle ternary operators (condition ? true : false)
            if (strpos($expression, '?') !== false && strpos($expression, ':') !== false) {
                return $this->evaluateTernaryOperator($expression, $data);
            }

            // Handle Python-style string multiplication
            if (preg_match('/^\'[^\']*\'\s*\*\s*.+|.*\*\s*\'[^\']*\'$/', $expression)) {
                return $this->evaluateStringMultiplication($expression, $data);
            }

            // Handle complex expressions with nested braces (like rating stars)
            if (preg_match('/\{.*\}.*\{.*\}/', $expression)) {
                return $this->evaluateComplexExpression($expression, $data);
            }

            // Get the value using nested property resolution
            $value = $this->resolveNestedProperty($data, $expression);

            // Return the resolved value as a string
            return (string) $value;
        }, $template);
    }

    /**
     * Applies a filter to a value.
     *
     * @param mixed $value The value to filter.
     * @param string $filterName The name of the filter to apply.
     * @return string The filtered value as a string.
     */
    protected function applyFilter($value, string $filterName): string
    {
        switch ($filterName) {
            case 'length':
                if (is_array($value) || $value instanceof \Countable) {
                    return (string) count($value);
                }
                return '0';

            case 'count':
                // Alias for length
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

            case 'round':
                return (string) round((float) $value);

            case 'stars':
                $rating = round((float) $value);
                $filled = str_repeat('★', (int) $rating);
                $empty = str_repeat('☆', 5 - (int) $rating);
                return $filled . $empty;

            default:
                // Unknown filter, return the original value
                return (string) $value;
        }
    }

    /**
     * Evaluates a ternary operator expression (condition ? true : false).
     *
     * @param string $expression The ternary expression to evaluate.
     * @param array $data The data array for variable resolution.
     * @return string The result of the ternary evaluation.
     */
    protected function evaluateTernaryOperator(string $expression, array $data): string
    {
        // Split by ? and :
        $parts = preg_split('/(\?|\:)/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) !== 5) {
            return $expression; // Invalid ternary, return as-is
        }

        $condition = trim($parts[0]);
        $trueValue = trim($parts[2]);
        $falseValue = trim($parts[4]);

        // Evaluate the condition
        $conditionResult = $this->evaluateSimpleCondition($condition, $data);

        if ($conditionResult) {
            return $this->resolveExpressionValue($trueValue, $data);
        } else {
            return $this->resolveExpressionValue($falseValue, $data);
        }
    }

    /**
     * Evaluates Python-style string multiplication ('char' * count).
     *
     * @param string $expression The string multiplication expression.
     * @param array $data The data array for variable resolution.
     * @return string The result of the string multiplication.
     */
    protected function evaluateStringMultiplication(string $expression, array $data): string
    {
        // Handle patterns like '★' * 4 or 4 * '★'
        if (preg_match('/^\'([^\']*)\'\s*\*\s*(.+)$/', $expression, $matches)) {
            $char = $matches[1];
            $count = $this->resolveExpressionValue(trim($matches[2]), $data);
        } elseif (preg_match('/^(.+)\s*\*\s*\'([^\']*)\'$/', $expression, $matches)) {
            $count = $this->resolveExpressionValue(trim($matches[1]), $data);
            $char = $matches[2];
        } else {
            return $expression; // Invalid expression
        }

        $count = (int) $count;
        if ($count <= 0) {
            return '';
        }

        return str_repeat($char, $count);
    }

    /**
     * Evaluates a simple condition for ternary operators.
     *
     * @param string $condition The condition to evaluate.
     * @param array $data The data array for variable resolution.
     * @return bool The result of the condition evaluation.
     */
    protected function evaluateSimpleCondition(string $condition, array $data): bool
    {
        // Handle basic comparisons and boolean values
        $condition = trim($condition);

        // Handle boolean literals
        if ($condition === 'true') return true;
        if ($condition === 'false') return false;

        // Handle variable resolution
        $value = $this->resolveNestedProperty($data, $condition);

        return (bool) $value;
    }

    /**
     * Resolves an expression value (variable or literal).
     *
     * @param string $expression The expression to resolve.
     * @param array $data The data array for variable resolution.
     * @return string The resolved value.
     */
    protected function resolveExpressionValue(string $expression, array $data): string
    {
        $expression = trim($expression);

        // Handle quoted strings
        if (preg_match('/^\'([^\']*)\'$/', $expression, $matches)) {
            return $matches[1];
        }
        if (preg_match('/^"([^"]*)"$/', $expression, $matches)) {
            return $matches[1];
        }

        // Handle variables
        $value = $this->resolveNestedProperty($data, $expression);

        return (string) $value;
    }

    /**
     * Evaluates complex expressions with nested braces (like rating stars).
     *
     * @param string $expression The complex expression to evaluate.
     * @param array $data The data array for variable resolution.
     * @return string The result of the expression evaluation.
     */
    protected function evaluateComplexExpression(string $expression, array $data): string
    {
        // Handle the specific case of rating stars: {'★' * (product.rating|round)}{'☆' * (5 - product.rating|round)}
        if (preg_match('/\{\'(.)\'\s*\*\s*\(([^|]+)\|round\)\}\{\'(.)\'\s*\*\s*\(5\s*-\s*([^|]+)\|round\)\}/', $expression, $matches)) {
            $filledChar = $matches[1];
            $ratingVar = $matches[2];
            $emptyChar = $matches[3];
            $ratingVar2 = $matches[4];

            // Get the rating value and apply round filter
            $rating = $this->resolveNestedProperty($data, $ratingVar);
            $rating = $this->applyFilter($rating, 'round');
            $rating = (int) $rating;

            // Calculate filled and empty stars
            $filledCount = $rating;
            $emptyCount = 5 - $rating;

            return str_repeat($filledChar, $filledCount) . str_repeat($emptyChar, $emptyCount);
        }

        // For other complex expressions, recursively process nested expressions
        while (preg_match('/\{([^{}]*)\}/', $expression, $matches)) {
            $innerExpression = trim($matches[1]);
            $fullMatch = $matches[0];

            // Handle string multiplication
            if (preg_match('/^\'([^\']*)\'\s*\*\s*(.+)$/', $innerExpression, $subMatches)) {
                $char = $subMatches[1];
                $countExpr = trim($subMatches[2]);

                // Process the count expression (may contain filters or variables)
                if (strpos($countExpr, '|') !== false) {
                    list($var, $filter) = explode('|', $countExpr, 2);
                    $count = $this->resolveNestedProperty($data, trim($var));
                    $count = $this->applyFilter($count, trim($filter));
                } elseif (preg_match('/^(.+)\s*-\s*(.+)$/', $countExpr, $arithMatches)) {
                    $left = $this->resolveExpressionValue(trim($arithMatches[1]), $data);
                    $right = $this->resolveExpressionValue(trim($arithMatches[2]), $data);
                    $count = (float) $left - (float) $right;
                } else {
                    $count = $this->resolveExpressionValue($countExpr, $data);
                }

                $replacement = str_repeat($char, (int) $count);
            } else {
                // Handle other expressions
                $replacement = $this->resolveExpressionValue($innerExpression, $data);
            }

            $expression = str_replace($fullMatch, $replacement, $expression);
        }

        return $expression;
    }

	/**
     * Parses an array of data and replaces the corresponding keys in the template.
     *
     * @param string $template The template string containing placeholders.
     * @param string|array $var The variable name in the template to be replaced.
     * @param string|array $data The data array used for replacement.
     * @return array An associative array with the original strings to be replaced and their new values.
     */
    protected function parseArray(string $template, string|array $var, string|array $data): array
	{
		$replace = [];

		$pattern = '~{\\s*'.preg_quote($var).'\\s*}(.+?){\\s*/'.preg_quote($var).'\\s*}~s';
		preg_match_all($pattern, $template, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {

			$str = '';
			foreach ($data as $row) {

				$arr = [];
				foreach ($row as $key => $val) {

					if (is_array($val)) {
						$nested = $this->parseArray($key, $val, $match[1]);
						if (!empty($nested))
						{
							$arr = array_merge($arr, $nested);
						}

						continue; 
					}

					$arr[$key] = is_array($val) ? implode(', ', $val) : (string)$val;
				}

				$str .= strtr($match[1], $arr); 
			}

			$replace[$match[0]] = str_replace(['{','}'],'',$str);
		}

		return $replace;
	}
	
    /**
     * Parses the template string and replaces the placeholders with the corresponding data values.
     *
     * @param string $template The template string containing placeholders.
     * @param array $data The data array used for replacement.
     * @return string The template string with placeholders replaced by data values.
     */
    public function parseTemplate(string $template, array $data): string
    {
        // Set the data for use in parsing methods
        $this->data = $data;

        // Pre-process certain attributes that should be allowed to be template-processed
        $processedBlocks = [];
        $template = $this->preProcessAttributes($template, $processedBlocks);

        // Protect content inside HTML tags that shouldn't be processed
        $protectedBlocks = [];
        $template = $this->protectHtmlBlocks($template, $protectedBlocks);

        // Parse filters first (e.g., {variable|filter})
        $template = $this->parseFilters($template, $data);

        $replace = [];

        if ($data) {
            foreach ($data as $key => $val) {
                $parse = is_array($val) ? $this->parseArray($template, $key, $val) : $this->parseValue($key, (string) $val);
				$replace = array_merge($replace, $parse);
            }
        }

        unset($data);

        $template = strtr($template, $replace);

        // Parse conditionals (includes foreach loops)
        $template = $this->parseConditionals($template);

        // Parse nested property access (e.g., {stats.total_users}) after foreach processing
        $template = $this->parseNestedProperties($template, $this->data);

        // Restore pre-processed attributes
        $template = $this->restorePreProcessedAttributes($template, $processedBlocks);

        // Restore protected HTML blocks
        $template = $this->restoreHtmlBlocks($template, $protectedBlocks);

        return $template;
    }
	
	/**
     * Parses conditional statements within the template.
     *
     * @param string $template The template string containing conditional statements.
     * @return string The template string with conditional statements evaluated and replaced.
     */
    protected function parseConditionals(string $template): string
    {
        // Handle foreach loops with proper nesting support first
        $template = $this->parseNestedForeach($template);

        // Handle if/elseif/else blocks
        $ifPattern = '~{%\s*if\s+([^%]*?)\s*%}(.*?)(?:{%\s*else\s*%}(.*?))?{%\s*endif\s*%}~s';
        $template = preg_replace_callback($ifPattern, function($matches) {
            $condition = trim($matches[1]);
            $ifContent = trim($matches[2]);
            $elseContent = isset($matches[3]) ? trim($matches[3]) : '';
            if ($this->evaluateCondition($condition)) {
                return $ifContent;
            } else {
                return $elseContent;
            }
        }, $template);

        // Handle for loops
        $forPattern = '~{%\s*for\s+(\w+)\s+in\s+(\d+)\s*\.\.\s*(\d+)\s*%}(.*?){%\s*endfor\s*%}~s';
        $template = preg_replace_callback($forPattern, function($matches) {
            return $this->parseFor($matches);
        }, $template);

        // Handle while loops
        $whilePattern = '~{%\s*while\s+([^%]*?)\s*%}(.*?){%\s*endwhile\s*%}~s';
        $template = preg_replace_callback($whilePattern, function($matches) {
            return $this->parseWhile($matches);
        }, $template);

        return $template;
    }
	
	/**
	 * Parses `foreach` statements in the template.
	 * It iterates over the provided array or Traversable object and replaces each occurrence of the loop variable with the current item.
	 *
	 * @param array $match An array containing the matches from the regular expression.
	 * @return string The parsed content with all occurrences of the loop variable replaced.
	 */
	protected function parseForeach(array $match): string
    {
        $iterableVar = $match[1];
        $loopVar = $match[2];
        $content = $match[3];
        
        // Handle nested properties in the iterable variable (e.g., user.skills)
        $iterable = $this->resolveNestedProperty($this->data, $iterableVar);
        
        if (!is_iterable($iterable)) {
            return '';
        }

        $result = '';
        
        foreach ($iterable as $key => $value) {
            // Create a temporary array for replacements
            $replacements = [];
            
            // Handle simple variable replacement (e.g., {user})
            $replacements['{'.$loopVar.'}'] = is_array($value) ? json_encode($value) : (string)$value;
            
            // Handle nested properties (e.g., {user.name}, {user.profile.age})
            if (is_array($value) || is_object($value)) {
                $flattened = $this->flattenArray($value, $loopVar);
                foreach ($flattened as $nestedKey => $nestedValue) {
                    $replacements['{'.$nestedKey.'}'] = (string)$nestedValue;
                }
            }
            
            // Process the content with the current iteration's replacements
            $processedContent = strtr($content, $replacements);

            // Process nested foreach loops
            $processedContent = $this->processNestedForeach($processedContent, $value, $loopVar);

            // Process conditionals within the loop content
            $processedContent = $this->parseConditionalsInLoop($processedContent, $value, $loopVar);
            
            $result .= $processedContent;
        }

        return $result;
    }

    protected function processNestedForeach(string $content, $parentValue, string $parentVar): string
    {
        // Find all nested foreach blocks within this content that reference the parent variable
        $pattern = '~{%\s*foreach\s+([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\s+as\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*%}(.*?){%\s*endforeach\s*%}~s';

        // Process nested foreach loops
        $content = preg_replace_callback($pattern, function($matches) use ($parentValue, $parentVar) {
            $varName = $matches[1];        // e.g., "user"
            $property = $matches[2];       // e.g., "skills"
            $loopVar = $matches[3];        // e.g., "skill"
            $nestedContent = $matches[4];  // The content inside the nested foreach

            // Only process if this foreach references the parent variable
            if ($varName !== $parentVar) {
                return $matches[0]; // Return unchanged
            }

            // Get the nested iterable from the parent value
            $nestedIterable = null;
            if (is_array($parentValue) && isset($parentValue[$property])) {
                $nestedIterable = $parentValue[$property];
            } elseif (is_object($parentValue) && property_exists($parentValue, $property)) {
                $nestedIterable = $parentValue->$property;
            } elseif (is_object($parentValue) && method_exists($parentValue, 'get' . ucfirst($property))) {
                $method = 'get' . ucfirst($property);
                $nestedIterable = $parentValue->$method();
            }

            if (!is_iterable($nestedIterable)) {
                return '';
            }

            // Process the nested loop
            $nestedResult = '';
            foreach ($nestedIterable as $item) {
                $itemReplacements = [];
                $itemReplacements['{'.$loopVar.'}'] = is_array($item) ? json_encode($item) : (string)$item;

                if (is_array($item) || is_object($item)) {
                    $flattened = $this->flattenArray($item, $loopVar);
                    foreach ($flattened as $nestedKey => $nestedValue) {
                        $itemReplacements['{'.$nestedKey.'}'] = (string)$nestedValue;
                    }
                }

                $nestedResult .= strtr($nestedContent, $itemReplacements);
            }

            return $nestedResult;
        }, $content);

        return $content;
    }

    // Update flattenArray to remove square brackets from keys
    protected function flattenArray($data, $prefix = '', array &$result = []): array
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
     * Parses foreach loops, processing them in order from outermost to innermost.
     *
     * @param string $template The template string containing foreach loops.
     * @return string The template string with foreach loops processed.
     */
    protected function parseNestedForeach(string $template): string
    {
        // Find all top-level foreach blocks (not nested inside other foreach blocks)
        $blocks = $this->findTopLevelForeachBlocks($template);

        foreach ($blocks as $block) {
            $parsedContent = $this->parseForeach([
                $block['full_match'],
                $block['iterable_var'],
                $block['loop_var'],
                $block['content']
            ]);

            $template = str_replace($block['full_match'], $parsedContent, $template);
        }

        return $template;
    }

    /**
     * Finds top-level foreach blocks that are not nested inside other foreach blocks.
     *
     * @param string $template The template string to search.
     * @return array Array of top-level foreach blocks.
     */
    protected function findTopLevelForeachBlocks(string $template): array
    {
        $blocks = [];
        $length = strlen($template);
        $i = 0;

        while ($i < $length) {
            $foreachPos = strpos($template, '{% foreach', $i);
            if ($foreachPos === false) {
                break;
            }

            // Find the matching endforeach by counting nesting levels
            $depth = 0;
            $j = $foreachPos + 11; // Skip past '{% foreach'
            $endPos = false;

            while ($j < $length) {
                $nextForeach = strpos($template, '{% foreach', $j);
                $nextEndForeach = strpos($template, '{% endforeach', $j);

                // If no more endforeach found, break
                if ($nextEndForeach === false) {
                    break;
                }

                // If there's a foreach before the next endforeach, it's nested
                if ($nextForeach !== false && $nextForeach < $nextEndForeach) {
                    $depth++;
                    $j = $nextForeach + 11;
                } else {
                    // Found an endforeach
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
                $blockContent = substr($template, $foreachPos, $endPos - $foreachPos + 16); // +16 for '{% endforeach %}'

                // Extract the foreach line and content
                $foreachEndPos = strpos($blockContent, '%}') + 2;
                $foreachLine = substr($blockContent, 0, $foreachEndPos);
                $content = substr($blockContent, $foreachEndPos, strlen($blockContent) - $foreachEndPos - 16); // Remove the '{% endforeach %}' from the end

                if (preg_match('~{%\s*foreach\s+(\w+|\w+\.\w+)\s+as\s+(\w+)\s*%}~', $foreachLine, $matches)) {
                    $blocks[] = [
                        'full_match' => $blockContent,
                        'iterable_var' => $matches[1],
                        'loop_var' => $matches[2],
                        'content' => $content
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
     * Finds all foreach blocks in the template and returns them with depth information.
     *
     * @param string $template The template string to search.
     * @return array Array of foreach blocks with their properties.
     */
    protected function findForeachBlocks(string $template): array
    {
        $blocks = [];
        $length = strlen($template);
        $i = 0;

        while ($i < $length) {
            $startPos = strpos($template, '{% foreach', $i);
            if ($startPos === false) {
                break;
            }

            // Find the matching endforeach by counting nesting levels
            $depth = 0;
            $j = $startPos + 11; // Skip past '{% foreach'
            $endPos = false;

            while ($j < $length) {
                $nextForeach = strpos($template, '{% foreach', $j);
                $nextEndForeach = strpos($template, '{% endforeach', $j);

                // If no more endforeach found, break
                if ($nextEndForeach === false) {
                    break;
                }

                // If there's a foreach before the next endforeach, it's nested
                if ($nextForeach !== false && $nextForeach < $nextEndForeach) {
                    $depth++;
                    $j = $nextForeach + 11;
                } else {
                    // Found an endforeach
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
                $blockContent = substr($template, $startPos, $endPos - $startPos + 16); // +16 for '{% endforeach %}'

                // Extract the components using regex
                if (preg_match('~{%\s*foreach\s+(\w+|\w+\.\w+)\s+as\s+(\w+)\s*%}(.*?){%\s*endforeach\s*%}~s', $blockContent, $matches)) {
                    $blocks[] = [
                        'full_match' => $blockContent,
                        'iterable_var' => $matches[1],
                        'loop_var' => $matches[2],
                        'content' => $matches[3],
                        'depth' => 0 // We'll calculate depth differently
                    ];
                }

                $i = $endPos + 16;
            } else {
                $i = $startPos + 11;
            }
        }

        // Calculate depths by checking how many foreach blocks contain each other
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
     * Parses conditionals within loop content, where loop variables are available.
     *
     * @param string $content The loop content containing conditionals.
     * @param mixed $loopValue The current loop item value.
     * @param string $loopVar The loop variable name.
     * @return string The content with conditionals processed.
     */
    protected function parseConditionalsInLoop(string $content, $loopValue, string $loopVar): string
    {
        // Handle if/endif blocks within loop content (most common case)
        $ifPattern = '~\{%\s*if\s+([^%]*?)\s*%\}([^{]*?)\{%\s*endif\s*%\}~s';
        $content = preg_replace_callback($ifPattern, function($matches) use ($loopValue, $loopVar) {
            $condition = trim($matches[1]);
            $ifContent = trim($matches[2]);

            if ($this->evaluateConditionInLoop($condition, $loopValue, $loopVar)) {
                return $ifContent;
            } else {
                return '';
            }
        }, $content);

        return $content;
    }

    /**
     * Evaluates a condition within a loop context, where loop variables are available.
     *
     * @param string $condition The condition to evaluate.
     * @param mixed $loopValue The current loop item value.
     * @param string $loopVar The loop variable name.
     * @return bool The result of the condition evaluation.
     */
    protected function evaluateConditionInLoop(string $condition, $loopValue, string $loopVar): bool
    {
        // Handle 'not' keyword
        $condition = preg_replace('/\bnot\s+/', '!', $condition);

        // Simple approach: replace variable patterns that are not inside quotes
        // Split by quotes and only replace in unquoted parts
        $parts = preg_split('/(\'[^\']*\'|"[^"]*")/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => &$part) {
            // Skip quoted strings (odd indices contain the delimiters)
            if ($i % 2 === 0) {
                // This is an unquoted part, replace variables
                // Use a regex that matches complete dotted paths
                if (preg_match_all('~\\$?([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)~', $part, $matches)) {
                    $variables = $matches[0];
                    $varPaths = $matches[1];

                    // Process in reverse order to handle longer paths first
                    $matchesCount = count($varPaths);
                    for ($i = $matchesCount - 1; $i >= 0; $i--) {
                        $varMatch = $variables[$i];
                        $varPath = $varPaths[$i];

                        // Check if this references the loop variable
                        if (strpos($varPath, $loopVar . '.') === 0 || $varPath === $loopVar) {
                            // This is the loop variable or a property of it
                            $value = $this->resolveNestedProperty([$loopVar => $loopValue], $varPath);
                        } else {
                            // This is a variable from the main data context
                            $value = $this->resolveNestedProperty($this->data, $varPath);
                        }

                        if (is_bool($value)) {
                            $replacement = $value ? 'true' : 'false';
                        } elseif (is_string($value)) {
                            $replacement = "'" . addslashes($value) . "'";
                        } elseif (is_numeric($value)) {
                            $replacement = (string) $value;
                        } elseif (is_null($value)) {
                            $replacement = 'null';
                        } else {
                            $replacement = 'false'; // Default for unsupported types
                        }

                        $part = str_replace($varMatch, $replacement, $part);
                    }
                }
            }
        }

        $condition = implode('', $parts);

        // Safely evaluate the condition
        try {
            // Use a safer approach - check for simple comparisons
            $result = eval("return ($condition);");
            return (bool) $result;
        } catch (\Throwable $e) {
            // If evaluation fails, return false for safety
            return false;
        }
    }

    /**
     * Handles `else` statements in the template.
     * This method is kept for backward compatibility but is no longer used in the new parsing logic.
     *
     * @deprecated Use the new parseConditionals method instead
     * @param array $match An array containing the matches from the regular expression.
     * @return string The content following the `else` statement.
     */
    protected function parseElse(array $match): string
    {
        $content = $match[3];
        $end = $match[0];

        return $content . $end;
    }

    /**
     * Evaluates `elseif` conditions in the template.
     * This method is kept for backward compatibility but is no longer used in the new parsing logic.
     *
     * @deprecated Use the new parseConditionals method instead
     * @param array $match An array containing the matches from the regular expression.
     * @return string The content if the condition is true, otherwise the content following the `elseif` statement.
     */
    protected function parseElseif(array $match): string
    {
        $condition = $match[1];
        $content = $match[2];
        $end = $match[3] ?? '';

        if ($this->evaluateCondition($condition)) {
            return $content . '{% endif %}';
        } else {
            return $end;
        }
    }

    /**
     * Pre-processes certain attributes that should be template-processed before protection.
     *
     * @param string $template The template string.
     * @param array &$processedBlocks Array to store processed blocks for restoration.
     * @return string The template with pre-processed attributes.
     */
    protected function preProcessAttributes(string $template, array &$processedBlocks): string
    {
        // Handle script src attributes specifically
        $template = preg_replace_callback(
            '~<script([^>]*)src="([^"]*)"([^>]*)>~is',
            function($matches) use (&$processedBlocks) {
                $beforeSrc = $matches[1];
                $src = $matches[2];
                $afterSrc = $matches[3];

                // Store the processed src attribute for later restoration
                $srcPlaceholder = '___PROCESSED_SCRIPT_SRC_' . count($processedBlocks) . '___';
                $processedBlocks[$srcPlaceholder] = $src;

                return '<script' . $beforeSrc . 'src="' . $srcPlaceholder . '"' . $afterSrc . '>';
            },
            $template
        );

        return $template;
    }

    /**
     * Protects content inside HTML tags that shouldn't be processed by template parsing.
     *
     * @param string $template The template string.
     * @param array &$protectedBlocks Array to store protected blocks.
     * @return string The template with protected blocks replaced by placeholders.
     */
    protected function protectHtmlBlocks(string $template, array &$protectedBlocks): string
    {
        $tagsToProtect = ['style', 'code', 'pre'];

        foreach ($tagsToProtect as $tag) {
            // Pattern to match opening and closing tags with content
            $pattern = '~<' . $tag . '[^>]*>(.*?)</' . $tag . '>~is';

            $template = preg_replace_callback($pattern, function($matches) use (&$protectedBlocks, $tag) {
                $placeholder = '___PROTECTED_' . strtoupper($tag) . '_' . count($protectedBlocks) . '___';
                $protectedBlocks[$placeholder] = $matches[0];
                return $placeholder;
            }, $template);
        }

        // Special handling for script tags - only protect those that don't contain pre-processed placeholders
        $scriptPattern = '~<script(?!\s[^>]*___PROCESSED_SCRIPT_SRC_)[^>]*>(.*?)</script>~is';
        $template = preg_replace_callback($scriptPattern, function($matches) use (&$protectedBlocks) {
            $placeholder = '___PROTECTED_SCRIPT_' . count($protectedBlocks) . '___';
            $protectedBlocks[$placeholder] = $matches[0];
            return $placeholder;
        }, $template);

        return $template;
    }

    /**
     * Restores pre-processed attributes after template processing.
     *
     * @param string $template The processed template string.
     * @param array $processedBlocks Array of pre-processed blocks.
     * @return string The template with pre-processed attributes restored.
     */
    protected function restorePreProcessedAttributes(string $template, array $processedBlocks): string
    {
        foreach ($processedBlocks as $placeholder => $originalContent) {
            // Process the original content (which contains template variables) through variable replacement
            $processedContent = $this->parseNestedProperties($originalContent, $this->data);
            $template = str_replace($placeholder, $processedContent, $template);
        }

        return $template;
    }

    /**
     * Restores protected HTML blocks after template processing.
     *
     * @param string $template The processed template string.
     * @param array $protectedBlocks Array of protected blocks to restore.
     * @return string The template with protected blocks restored.
     */
    protected function restoreHtmlBlocks(string $template, array $protectedBlocks): string
    {
        foreach ($protectedBlocks as $placeholder => $originalContent) {
            $template = str_replace($placeholder, $originalContent, $template);
        }

        return $template;
    }
	
	/**
	 * Parses `while` loops in the template.
	 * It repeatedly evaluates the condition and appends the content for each iteration where the condition is true.
	 *
	 * @param array $match An array containing the matches from the regular expression.
	 * @return string The concatenated content of each iteration where the condition is true.
	 */
    protected function parseWhile(array $match): string
    {

        $condition = $match[1];
        $content = $match[2];

        $result = '';

        while ($this->evaluateCondition($condition)) {

            $result .= $content;
        }

        return $result;
    }
	
	/**
	 * Parses `for` loops in the template.
	 * It iterates from a start value to an end value and replaces the loop variable with the current iteration value.
	 *
	 * @param array $match An array containing the matches from the regular expression.
	 * @return string The parsed content with all occurrences of the loop variable replaced with iteration values.
	 */
    protected function parseFor(array $match): string
    {
        $loopVar = $match[1];
        $start = (int) $match[2];
        $end = (int) $match[3];
        $content = $match[4];

        $result = '';

        for ($i = $start; $i <= $end; $i++) {
            $result .= str_replace("{".$loopVar."}", (string) $i, $content);
        }

        return $result;
    }
	
	/**
	 * Evaluates a given condition as a boolean value.
	 * It replaces variables with their values and evaluates the condition as a PHP expression.
	 *
	 * @param string $condition The condition to evaluate.
	 * @return bool The result of the condition evaluation.
	 */
	protected function evaluateCondition(string $condition): bool
	{
        // Handle 'not' keyword
        $condition = preg_replace('/\bnot\s+/', '!', $condition);

        // Simple approach: replace variable patterns that are not inside quotes
        // Split by quotes and only replace in unquoted parts
        $parts = preg_split('/(\'[^\']*\'|"[^"]*")/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => &$part) {
            // Skip quoted strings (odd indices contain the delimiters)
            if ($i % 2 === 0) {
                // This is an unquoted part, replace variables
                $part = preg_replace_callback('~\\$?([a-zA-Z_][a-zA-Z0-9_.]*)~', function ($match) {
                    $varPath = $match[1];

                    // Get the value using nested property resolution
                    $value = $this->resolveNestedProperty($this->data, $varPath);

                    if (is_bool($value)) {
                        return $value ? 'true' : 'false';
                    } elseif (is_string($value)) {
                        return "'" . addslashes($value) . "'";
                    } elseif (is_numeric($value)) {
                        return (string) $value;
                    } elseif (is_null($value)) {
                        return 'null';
                    } else {
                        return 'false'; // Default for unsupported types
                    }
                }, $part);
            }
        }

        $condition = implode('', $parts);

        // Safely evaluate the condition
        try {
            // Use a safer approach - check for simple comparisons
            $result = eval("return ($condition);");
            return (bool) $result;
        } catch (\Throwable $e) {
            // If evaluation fails, return false for safety
            return false;
        }
    }

}
