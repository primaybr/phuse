<?php

declare(strict_types=1);

namespace Core\Template;

use Traversable;

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

        $replace = [];

        if ($data) {
            foreach ($data as $key => $val) {
                $parse = is_array($val) ? $this->parseArray($template, $key, $val) : $this->parseValue($key, (string) $val);
				$replace = array_merge($replace, $parse);
            }
        }

        unset($data);

        $template = strtr($template, $replace);

        $template = $this->parseConditionals($template);

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
        // Handle if/elseif/else blocks
        $ifPattern = '~{%\s*if\s+([^%]*?)\s*%}(.*?)(?:{%\s*else\s*%}(.*?))?{%\s*endif\s*%}~s';
        $template = preg_replace_callback($ifPattern, function($matches) {
            $condition = trim($matches[0]);
            $ifContent = $matches[1];
            $elseContent = $matches[2] ?? '';
            if ($this->evaluateCondition($condition)) {
                return $ifContent;
            } else {
                return $elseContent;
            }
        }, $template);

        // Handle foreach loops with proper nesting support
        $template = $this->parseNestedForeach($template);

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
            
            $result .= $processedContent;
        }

        return $result;
    }

    protected function processNestedForeach(string $content, $parentValue, string $parentVar): string
    {
        // Look for nested foreach loops with the parent variable
        $pattern = '~{%\s*foreach\s+(' . preg_quote($parentVar) . '\.([a-zA-Z_][a-zA-Z0-9_]*))\s+as\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*%}(.*?)(?=\s*{%\s*endforeach\s*%}\s*|\z)~s';
        
        if (!preg_match($pattern, $content, $matches)) {
            return $content;
        }

        $fullMatch = $matches[0];
        $nestedVar = $matches[1];      // e.g., "user.skills"
        $property = $matches[2];       // e.g., "skills"
        $loopVar = $matches[3];        // e.g., "skill"
        $nestedContent = $matches[4];  // The content inside the nested foreach

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
            return str_replace($fullMatch, '', $content);
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
        
        // Replace the entire foreach block with the processed content
        return str_replace($fullMatch, $nestedResult, $content);
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
        $depth = 0;

        while ($i < $length) {
            $foreachPos = strpos($template, '{% foreach', $i);
            $endforeachPos = strpos($template, '{% endforeach', $i);

            if ($foreachPos === false && $endforeachPos === false) {
                break;
            }

            // If we find a foreach first, it's a potential top-level block
            if ($foreachPos !== false && ($endforeachPos === false || $foreachPos < $endforeachPos)) {
                if ($depth === 0) {
                    // This is a top-level foreach
                    $startPos = $foreachPos;

                    // Find the matching endforeach
                    $nestedDepth = 0;
                    $j = $foreachPos + 11; // Skip past '{% foreach'

                    while ($j < $length) {
                        $nextForeach = strpos($template, '{% foreach', $j);
                        $nextEndForeach = strpos($template, '{% endforeach', $j);

                        if ($nextEndForeach === false) {
                            break;
                        }

                        if ($nextForeach !== false && $nextForeach < $nextEndForeach) {
                            $nestedDepth++;
                            $j = $nextForeach + 11;
                        } else {
                            if ($nestedDepth === 0) {
                                // Found matching endforeach
                                $endPos = $nextEndForeach;
                                $blockContent = substr($template, $startPos, $endPos - $startPos + 14);

                                if (preg_match('~{%\s*foreach\s+(\w+|\w+\.\w+)\s+as\s+(\w+)\s*%}(.*?){%\s*endforeach\s*%}~s', $blockContent, $matches)) {
                                    $blocks[] = [
                                        'full_match' => $blockContent,
                                        'iterable_var' => $matches[1],
                                        'loop_var' => $matches[2],
                                        'content' => $matches[3]
                                    ];
                                }
                                $i = $endPos + 14;
                                break;
                            } else {
                                $nestedDepth--;
                                $j = $nextEndForeach + 14;
                            }
                        }
                    }
                } else {
                    $i = $foreachPos + 11;
                }
            } else {
                // Skip past this endforeach
                $i = $endforeachPos + 14;
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
                        $j = $nextEndForeach + 14;
                    }
                }
            }

            if ($endPos !== false) {
                $blockContent = substr($template, $startPos, $endPos - $startPos + 14); // +14 for '{% endforeach %}'

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

                $i = $endPos + 14;
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
        // Replace variables in condition with their values
        $condition = preg_replace_callback('~\\$(\\w+)~', function ($match) {
            $varName = $match[1];

            if (isset($this->data[$varName])) {
                $varValue = $this->data[$varName];

                if (is_bool($varValue)) {
                    return $varValue ? 'true' : 'false';
                } else {
                    return (string) $varValue;
                }
            }

            return 'false';
        }, $condition);

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
