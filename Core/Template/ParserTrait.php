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

        return ["{{$key}}" => $val];
    }
	
	/**
     * Parses an array of data and replaces the corresponding keys in the template.
     *
     * @param string $template The template string containing placeholders.
     * @param string $var The variable name in the template to be replaced.
     * @param array $data The data array used for replacement.
     * @return array An associative array with the original strings to be replaced and their new values.
     */
    protected function parseArray(string $template, string $var, array $data): array
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

					$arr[$key] = $val;
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

        $foreachPattern = '~{%\\s*foreach\\s+(\\w+)\\s+as\\s+(\\w+)\\s*%}(.+?){%\\s*endforeach\\s*%}~s';
        $ifPattern = '~{%\\s*if\\s+(.+?)\\s*%}(.+?){%\\s*endif\\s*%}~s';
        $elsePattern = '~{%\\s*else\\s*%}(.+?)({%\\s*endif\\s*%}|{%\\s*elseif\\s+.+?\\s*%})~s';
        $elseifPattern = '~{%\\s*elseif\\s+(.+?)\\s*%}(.+?)({%\\s*endif\\s*%}|{%\\s*else\\s*%}|{%\\s*elseif\\s+.+?\\s*%})~s';
        $whilePattern = '~{%\\s*while\\s+(.+?)\\s*%}(.+?){%\\s*endwhile\\s*%}~s';
        $forPattern = '~{%\\s*for\\s+(\\w+)\\s*=\\s*(\\d+)\\s*to\\s*(\\d+)\\s*%}(.+?){%\\s*endfor\\s*%}~s';

        $template = preg_replace_callback($foreachPattern, [$this, 'parseForeach'], $template);
		
        $template = preg_replace_callback($ifPattern, [$this, 'parseIf'], $template);
        $template = preg_replace_callback($elsePattern, [$this, 'parseElse'], $template);
        $template = preg_replace_callback($elseifPattern, [$this, 'parseElseif'], $template);
        $template = preg_replace_callback($whilePattern, [$this, 'parseWhile'], $template);
        $template = preg_replace_callback($forPattern, [$this, 'parseFor'], $template);

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

		if (!isset($this->data[$iterableVar])) {

			return '';
		}

		$iterable = $this->data[$iterableVar];
		
		if (is_array($iterable) || $iterable instanceof Traversable) {

			$result = '';
			
			preg_match_all("/\{$loopVar\\.([^\\}]*)\}/",$content,$matched);
			
			if(isset($matched[1]))
			{
				$replaceable = $matched[1];
				
				foreach ($iterable as $key => $value) {
					
					//remove unused array
					foreach($value as $k => $v)
					{
						if(!in_array($k,$replaceable))
							unset($value[$k]);
					}
					
					$result .= strtr($content,$value);
				}
				
				$result = preg_replace("/\{$loopVar\\.([^\\}]*)\}/",'$1',$result);
				
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

        $condition = $match[1];
        $content = $match[2];

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

        $content = $match[1];
        $end = $match[2];

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

        $condition = $match[1];
        $content = $match[2];
        $end = $match[3];

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
