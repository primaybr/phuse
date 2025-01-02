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

/**
 * The Controller class is the base class for all controllers in the application.
 * It provides common functionality and services for controllers.
 */
class Controller extends \stdClass
{
    /** @var object The configuration object. */
    public object $config;

    /** @var Log The log object. */
    public Log $log;

    /** @var Session The session object. */
    public Session $session;

    /** @var Parser The template parser object. */
    public Parser $template;

    /** @var URI The URI object. */
    public URI $uri;

    /** @var Debug The debug object. */
    public Debug $debug;

    /** @var Input The input object. */
    public Input $input;

    /** @var Str The string utility object. */
    public Str $str;

    /** @var Folder The folder utility object. */
    public Folder $folder;

    /** @var Error The error object. */
    public Error $error;

    /** @var Number The number utility object. */
    public Number $textNumber;

    /** @var Cache The cache object. */
    public Cache $cache;

    /** @var string The base URL of the application. */
    public string $baseUrl;

    /** @var string The image URL of the application. */
    public string $imgUrl;

    /** @var string The assets URL of the application. */
    public string $assetsUrl;

    /**
     * Initializes the controller with the necessary dependencies.
     */
    public function __construct()
    {
        // check for php version required to run the framework
		if (version_compare(phpversion(), '8.2.0', '<='))
		{
			die('Minimum of PHP 8.2 is needed to run the framework, your php version is '.phpversion().' please upgrade your system!');
		}
        
        $this->config = (new Config)->get();
        $this->log = new Log();
        $this->session = new Session();
        $this->template = new Parser();
        $this->uri = new URI();
        $this->debug = new Debug();
        $this->input = new Input();
        $this->str = new Str();
        $this->folder = new Folder();
        $this->error = new Error();
        $this->textNumber = new Number();
        $this->cache = new Cache();

        $this->baseUrl = $this->config->site->baseUrl;
        $this->imgUrl = $this->config->site->imgUrl;
        $this->assetsUrl = $this->baseUrl . $this->config->site->assetsUrl . '/';
    }

    /**
     * Renders an HTML template with the provided data.
     *
     * @param string $template Path of the template.
     * @param array $data Data to render.
     * @param bool $return Flag to display data processed by template.
     * @return ?string Rendered template content or null.
     */
    public function render(string $template, array $data = [], bool $return = false): ?string
    {
        $data = array_merge($data, [
            'baseUrl' => $this->baseUrl,
            'imgUrl' => $this->imgUrl,
            'assetsUrl' => $this->assetsUrl,
        ]);

        return $this->template->render($template, $data, $return);
    }

    /**
     * Creates a model object based on the provided table name.
     *
     * @param array|string $table Table name or an array of tables.
     * @param string $database Database name.
     * @return Controller
     */
    public function model(array|string $table, string $database = 'default'): self
    {
        if ($database === 'default') {
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
     * Assigns a new object alias to the model and removes the old model object.
     *
     * @param string $table Table name.
     * @param string $alias Alias name.
     * @param string $database Database name.
     * @return Controller
     */
    public function modelAlias(string $table, string $alias, string $database = 'default'): self
    {
        if (!isset($this->{$table})) {
            $this->model($table, $database);
        }

        $this->{$alias} = $this->{$table};
        unset($this->{$table});

        return $this;
    }
}
