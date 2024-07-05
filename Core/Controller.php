<?php

declare(strict_types=1);

namespace Core;

use Core\Config;
use Core\Http\URI;
use Core\Http\Session;
use Core\Template\Parser;
use Core\Log;
use Core\Debug;
use Core\Http\Input;
use Core\Text\Str;
use Core\Folder\Folder;
use Core\Exception\Error;
use Core\Text\Number;
use Core\Cache\Cache;
use Core\Model;

class Controller extends \StdClass
{
    public $config;
    public Model $model;
    public Log $log;
    public Session $session;
    public Parser $template;
	public URI $uri;
	public Debug $debug;
	public Input $input;
	public Str $str;
	public Folder $folder;
	public Error $error;
	public Number $textNumber;
	public Cache $cache;
	public string $baseUrl;
    public string $imgUrl;
	public string $assetsUrl;

    public function __construct()
    {
		$this->config = (new Config)->get();
		$this->uri = new URI;
		$this->session = new Session;
        $this->template = new Parser;
        $this->log = new Log;
		$this->debug = new Debug;
		$this->input = new Input;
		$this->str = new Str;
		$this->folder = new Folder;
		$this->error = new Error;
		$this->textNumber = new Number;
		$this->cache = new Cache;
		
        $this->baseUrl = $this->config->site->baseUrl;
        $this->imgUrl = $this->config->site->imgUrl;
		
		$this->assetsUrl = $this->baseUrl . $this->config->site->assetsUrl . '/';
    }
	
	/**
     * The "render" method.
	 * Assign html template with object data
     *
     * @param string	$template    path of template
     * @param array		$data data to render
	 * @param bool	$return flag to display data processed by template
     *
     * @return ?string
     */
    public function render(string $template, array $data = [], bool $return = false): ?string
    {
        $data = array_merge($data, [
            'baseUrl' => $this->baseUrl,
            'imgUrl'	=> $this->imgUrl,
			'assetsUrl' => $this->assetsUrl
        ]);

        return $this->template->render($template, $data, $return);
    }

    /**
     * The "model" method.
	 * Create model object based on the declaration of $table
     *
     * @param array|string $table    table name, or an array of table
     * @param string       $database database name
     *
     * @return Controller
     */
    public function model(array|string $table, string $database = "default"): self
    {
        if ('default' === $database) {
            if (is_array($table)) {
                foreach ($table as $val) {
                    $this->{$val} = new Model($val, $database);
                }
            } else {
                $this->{$table} = new Model($table, $database);
            }
        } else {
            $this->{$database} = new \stdClass();

            if (is_array($table)) {
                foreach ($table as $val) {
                    $this->{$database}->{$val} = new Model($val, $database);
                }
            } else {
                $this->{$database}->{$table} = new Model($table, $database);
            }
        }

        return $this;
    }
	
	
	/**
     * The "modelAlias" method.	
     * Give new object alias to model, and remove the old model object
	 * 
     * @param string 	   $table    table name
	 * @param string 	   $alias    alias name
     * @param string       $database database name
     *
     * @return Controller
     */
	public function modelAlias(string $table, string $alias, string $database = 'default'): self
	{
		//check if there are no declaration of the table before
		if (!isset($this->{$table})) {
			$this->model($table, $database);
		}
		
		$this->{$alias} = $this->{$table};
		unset($this->{$table});
		
		return $this;
	}
}

