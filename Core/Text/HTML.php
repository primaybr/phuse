<?php

declare(strict_types=1);

namespace Core\Text;

class HTML{
	
	protected $css;
	protected $js;
	
	public function __construct()
	{
		$this->css = new CSS;
		$this->js = new JS;
	}
	
    private function setHeaders(){
        header('Content-Type: text/html; charset=UTF-8');
    }

    public function minify($input,$js=true,$css=true){
		$this->setHeaders();
		
		if(trim($input) === "") return $input;
		// Remove extra white-space(s) between HTML attribute(s)
		$input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
			return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
		}, str_replace("\r", "", $input));
		// Minify inline CSS declaration(s)
		if(strpos($input, ' style=') !== false) {
			$input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
				return '<' . $matches[1] . ' style=' . $matches[2] . $this->css->minifyCSS($matches[3]) . $matches[2];
			}, $input);
		}
		if(strpos($input, '</style>') !== false) {
		  $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function($matches) {
			return '<style' . $matches[1] .'>'. $this->css->minifyCSS($matches[2]) . '</style>';
		  }, $input);
		}
		// under development
		
		if(strpos($input, '</script>') !== false) {
		  $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function($matches) {
			return '<script' . $matches[1] .'>'. $this->js->minify($matches[2]) . '</script>';
		  }, $input);
		}
		

		return preg_replace(
			array(
				// t = text
				// o = tag open
				// c = tag close
				// Keep important white-space(s) after self-closing HTML tag(s)
				'#<(img|input)(>| .*?>)#s',
				// Remove a line break and two or more white-space(s) between tag(s)
				'#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
				'#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
				'#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
				'#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
				'#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
				'#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
				'#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
				'#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
				// Remove HTML comment(s) except IE comment(s)
				'#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
			),
			array(
				'<$1$2</$1>',
				'$1$2$3',
				'$1$2$3',
				'$1$2$3$4$5',
				'$1$2$3$4$5$6$7',
				'$1$2$3',
				'<$1$2',
				'$1 ',
				'$1',
				""
			),
		$input);
		
    }

}
