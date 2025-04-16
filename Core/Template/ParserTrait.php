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

        return ["{{$key}}" => $val];
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
        // Use a single regex pattern to match all conditional statements
        $pattern = '~{%\s*(if|elseif|else|foreach|for|while)\s+(.*?)\s+as\s+(.*?)\s*%}(.*?){%\s*(endif|endforeach|endfor|endwhile)\s*%}~s';

        return preg_replace_callback($pattern, function($matches) {
            return match ($matches[1]) {
                'if' => $this->parseIf($matches),
                'elseif' => $this->parseElseif($matches),
                'else' => $this->parseElse($matches),
                'foreach' => $this->parseForeach($matches),
                'for' => $this->parseFor($matches),
                'while' => $this->parseWhile($matches),
            };
        }, $template);
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
		
		$iterableVar = $match[2];
		$loopVar = $match[3];
		$content = $match[4];

		if (!isset($this->data[$iterableVar])) {

			return '';
		}

		$iterable = $this->data[$iterableVar];
		
		if (is_array($iterable) || $iterable instanceof Traversable) {

			$result = '';
            
            // Find all array key references
            preg_match_all("/\{$loopVar\.([^\}]*)\}/", $content, $matched);
            
            if (isset($matched[1])) {
                $replaceable = $matched[1];
                
                foreach ($iterable as $key => $value) {
                    // Create a temporary array for replacements
                    $tempArr = [];
                    
                    foreach ($value as $k => $v) {
                        // Only replace if the key exists in the template
                        if (in_array($k, $replaceable)) {
                            $tempArr[$k] = is_array($v) ? implode(', ', $v) : (string)$v;
                        }
                    }
                    
                    // Replace only the exact matches
                    $result .= preg_replace_callback("/\{$loopVar\.([^\}]*)\}/", function($m) use ($tempArr) {
                        return isset($tempArr[$m[1]]) ? $tempArr[$m[1]] : $m[0];
                    }, $content);
                }
                
                // Remove the loop variable prefix after replacements
                $result = preg_replace("/\{$loopVar\.([^\}]*)\}/", '$1', $result);
            }
            
            return $result;
        } else {

			return '';
        }
    }

	/**
	 * Evaluates `if` conditions in the template.
	 * It checks the condition and returns the content if the condition is true, otherwise, it returns an empty string.
	 *
	 * @param array $match An array containing the matches from the regular expression.
	 * @return string The content if the condition is true, otherwise an empty string.
	 */
    protected function parseIf(array $match): string
    {

        $condition = $match[2];
        $content = $match[4];

        if ($this->evaluateCondition($condition)) {

            return $content;
        } else {

            return '';
        }
    }
	
	/**
	 * Handles `else` statements in the template.
	 * It returns the content following an `else` statement.
	 *
	 * @param array $match An array containing the matches from the regular expression.
	 * @return string The content following the `else` statement.
	 */
    protected function parseElse(array $match): string
    {

        $content = $match[4];
        $end = $match[5];

        return $content . $end;
    }
	
	/**
	 * Evaluates `elseif` conditions in the template.
	 * Similar to `parseIf`, but for `elseif` statements. It checks the condition and returns the content if the condition is true.
	 *
	 * @param array $match An array containing the matches from the regular expression.
	 * @return string The content if the condition is true, otherwise the content following the `elseif` statement.
	 */
    protected function parseElseif(array $match): string
    {

        $condition = $match[2];
        $content = $match[4];
        $end = $match[5];

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

        $condition = $match[2];
        $content = $match[4];

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

        $loopVar = $match[2];
        $start = (int) $match[3];
        $end = (int) $match[4];
        $content = $match[5];

        $result = '';

        for ($i = $start; $i <= $end; $i++) {

            $result .= str_replace("{{$loopVar}}", (string) $i, $content);
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

		$condition = preg_replace_callback('~\\$(\\w+)~', function ($match) {

			$varName = $match[1];

			if (isset($this->$varName)) {

				$varValue = $this->$varName;

				if (is_bool($varValue)) {

					return $varValue ? 'true' : 'false';
				} else {

					return (string) $varValue;
				}
			} else {

				return '';
			}
		}, $condition);

		return $condition ? true : false;
	}

}
