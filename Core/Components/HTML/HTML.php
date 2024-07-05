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
use Core\Components\HTML\Lists;
use Core\Components\HTML\Main;
use Core\Components\HTML\Meta;
use Core\Components\HTML\Paragraph;
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
	//declare property to store the required Components
	protected Audio $audio;
	protected Button $button;
	protected Canvas $canvas;
	protected Code $code;
	protected Div $div;
	protected Document $document;
	protected Embed $embed;
	protected Footer $footer;
	protected Head $head;
	protected Header $header;
	protected Heading $heading;
	protected Img $img;
	protected Input $input;
	protected Label $label;
	protected Link $link;
	protected Lists $lists;
	protected Main $main;
	protected Meta $meta;
	protected Paragraph $paragraph;
	protected Script $script;
	protected Section $section;
	protected Select $select;
	protected Span $span;
	protected Style $style;
	protected Table $table;
	protected Iframe $iframe;
	protected Video $video;
	
		
	public function a(string $url, string $text): A
	{
		$this->a = new A($content);
		
		return $this->a;
	}
	
	public function audio(string $src, string $type): Audio
	{
		$this->audio = new Audio($src, $type);
		
		return $this->audio;
	}
		
	public function button(string $content): Button
	{
		$this->button = new Button($content);
		
		return $this->button;
	}
	
	public function canvas(string $script): Canvas
	{
		$this->canvas = new Canvas($script);
		
		return $this->canvas;
	}
	
	public function code(string $content): Code
	{
		$this->code = new Canvas($content);
		
		return $this->code;
	}
	
	public function div(string $content): Div
	{
		$this->div = new Div($content);
		
		return $this->div;
	}
	
	public function document(string $head): Document
	{
		$this->document = new Document(new Head($head));
		
		return $this->document;
	}
	
	public function embed(string $src, string $type): Embed
	{
		$this->embed = new Embed($src, $type);
		
		return $this->embed;
	}
	
	public function footer(string $content): Footer
	{
		$this->footer = new Footer($content);
		
		return $this->footer;
	}
	
	public function form(string $action, string $method, Validator $validator): Form
	{
		$this->form = new Form($action, $method, $validator);
		
		return $this->form;
	}
	
	public function head(string $title = ''): Head
	{
		$this->head = new Head($title);
		
		return $this->head;
	}
	
	public function header(string $content): Header
	{
		$this->header = new Header($content);
		
		return $this->header;
	}
	
	public function heading(int $level, string $content): Heading
	{
		$this->heading = new Heading($level, $content);
		
		return $this->heading;
	}
	
	public function iframe(string $src, string $title): Iframe
	{
		$this->iframe = new Iframe($src, $title);
		
		return $this->iframe;
	}
	
	public function img(string $src, string $alt): Img
	{
		$this->img = new Img($src, $alt);
		
		return $this->img;
	}
	
	public function input(string $type, string $name, string $value): Input
	{
		$this->input = new Input($type, $name, $value);
		
		return $this->input;
	}
	
	public function label(string $content): Label
	{
		$this->label = new Label($content);
		
		return $this->label;
	}
	
	public function link(string $src): Link
	{
		$this->link = new Link($src);
		
		return $this->link;
	}
	
	public function lists(string $type): Lists
	{
		$this->lists = new Lists($type);
		
		return $this->lists;
	}
	
	public function main(string $content): Main
	{
		$this->main = new Main($content);
		
		return $this->main;
	}
	
	public function meta(string $content): Meta
	{
		$this->meta = new Meta($content);
		
		return $this->meta;
	}
	
	public function p(string $content): P
	{
		$this->p = new P($content);
		
		return $this->p;
	}
	
	public function script(string $src, string $content = ''): Script
	{
		$this->script = new Script($type);
		
		return $this->script;
	}
	
	public function section(string $content): Section
	{
		$this->section = new Section($content);
		
		return $this->section;
	}
	
	public function select(): Select
	{
		$this->select = new Select();
		
		return $this->select;
	}
	
	public function span(string $content): Span
	{
		$this->span = new Span($content);
		
		return $this->span;
	}
	
	public function style(string $content): Style
	{
		$this->style = new Style($content);
		
		return $this->style;
	}
	
	public function table(): Table
	{
		$this->table = new Table($content);
		
		return $this->table;
	}
	
	public function video(string $src, string $type): Video
	{
		$this->video = new Video($src, $type);
		
		return $this->video;
	}
	
	public function render(string $component): void
	{
		echo $this->{$component}->render();
	}
	
}
