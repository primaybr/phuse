<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Use All the components present
use Core\Components\HTML\A;
use Core\Components\HTML\Audio;
use Core\Components\HTML\Button;
use Core\Components\HTML\Canvas;
use Core\Components\HTML\Code;
use Core\Components\HTML\Div;
use Core\Components\HTML\Document;
use Core\Components\HTML\Embed;
use Core\Components\HTML\Footer;
use Core\Components\HTML\Form;
use Core\Components\HTML\Head;
use Core\Components\HTML\Header;
use Core\Components\HTML\Heading;
use Core\Components\HTML\Img;
use Core\Components\HTML\Input;
use Core\Components\HTML\Label;
use Core\Components\HTML\Link;
use Core\Utilities\Validator\Validator;
use Core\Components\HTML\Main;
use Core\Components\HTML\Meta;
use Core\Components\HTML\Script;
use Core\Components\HTML\Section;
use Core\Components\HTML\Select;
use Core\Components\HTML\Span;
use Core\Components\HTML\Stylesheet;
use Core\Components\HTML\Table;
use Core\Components\HTML\Iframe;
use Core\Components\HTML\Video;

// Define a class for main HTML Components
class HTML
{
	
		
	public function a(string $url, string $text): A
	{
		return new A($url, $text);
	}
	
	public function audio(string $src, string $type): Audio
	{
		return new Audio($src, $type);
	}
		
	public function button(string $content): Button
	{
		return new Button($content);
	}
	
	public function canvas(string $script): Canvas
	{
		return new Canvas($script);
	}
	
	public function code(string $content): Code
	{
		return new Code($content);
	}
	
	public function div(string $content): Div
	{
		return new Div($content);
	}
	
	public function document(string $head): Document
	{
		return new Document(new Head($head));
	}
	
	public function embed(string $src, string $type): Embed
	{
		return new Embed($src, $type);
	}
	
	public function footer(string $content): Footer
	{
		return new Footer($content);
	}
	
	public function form(string $action, string $method, Validator $validator): Form
	{
		return new Form($action, $method, $validator);
	}
	
	public function head(string $title = ''): Head
	{
		return new Head($title);
	}
	
	public function header(string $content): Header
	{
		return new Header($content);
	}
	
	public function heading(int $level, string $content): Heading
	{
		return new Heading($level, $content);
	}
	
	public function iframe(string $src, string $title): Iframe
	{
		return new Iframe($src, $title);
	}
	
	public function img(string $src, string $alt): Img
	{
		return new Img($src, $alt);
	}
	
	public function input(string $type, string $name, string $value): Input
	{
		return new Input($type, $name, $value);
	}
	
	public function label(string $content): Label
	{
		return new Label($content);
	}
	
	public function link(string $src): Link
	{
		return new Link($src);
	}
	
	public function lists(string $type): Lists
	{
		return new Lists($type);
	}
	
	public function main(string $content): Main
	{
		return new Main($content);
	}
	
	public function meta(string $content): Meta
	{
		return new Meta($content);
	}
	
	public function p(string $content): P
	{
		return new P($content);
	}
	
	public function script(string $src, string $content = ''): Script
	{
		return new Script($src, $content);
	}
	
	public function section(string $content): Section
	{
		return new Section($content);
	}
	
	public function select(): Select
	{
		return new Select();
	}
	
	public function span(string $content): Span
	{
		return new Span($content);
	}
	
	public function style(string $content): Style
	{
		return new Style($content);
	}
	
	public function table(): Table
	{
		return new Table();
	}
	
	public function video(string $src, string $type): Video
	{
		return new Video($src, $type);
	}
	
}
