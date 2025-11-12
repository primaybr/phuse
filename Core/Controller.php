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
use Core\Utilities\Text\Str;
use Core\Folder\Folder;
use Core\Exception\Error;
use Core\Utilities\Text\Number;
use Core\Cache\CacheManager as Cache;
use Core\Model;
use Core\Security\CSRF;

/**
 * The Controller class is the base class for all controllers in the application.
 * It provides common functionality and services for controllers.
 *
 * This class serves as a foundation for all application controllers, providing
 * access to essential framework services like configuration, logging, session management,
 * template rendering, CSRF protection, and database operations.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Controller extends \stdClass
{
    /** 
     * @var object The configuration object containing all application settings.
     */
    public object $config;

    /** 
     * @var Log The log object for application logging functionality.
     */
    public Log $log;

    /** 
     * @var Session The session object for managing user sessions.
     */
    public Session $session;

    /** 
     * @var Parser The template parser object for rendering views.
     */
    public Parser $template;

    /** 
     * @var URI The URI object for handling request URIs and routing.
     */
    public URI $uri;

    /** 
     * @var Debug The debug object for debugging and error handling.
     */
    public Debug $debug;

    /** 
     * @var Input The input object for handling user input and request data.
     */
    public Input $input;

    /** 
     * @var Str The string utility object for string manipulation operations.
     */
    public Str $str;

    /** 
     * @var Folder The folder utility object for file system operations.
     */
    public Folder $folder;

    /** 
     * @var Error The error object for exception and error handling.
     */
    public Error $error;

    /** 
     * @var Number The number utility object for number formatting and calculations.
     */
    public Number $textNumber;

    /** 
     * @var CSRF The CSRF protection object for generating and validating tokens.
     */
    public CSRF $csrf;

    /** 
     * @var Cache The cache object for caching data.
     */
    public $cache;

    /** 
     * @var string The base URL of the application for generating absolute URLs.
     */
    public string $baseUrl;

    /** 
     * @var string The image URL of the application for referencing image assets.
     */
    public string $imgUrl;

    /** 
     * @var string The assets URL of the application for referencing static assets.
     */
    public string $assetsUrl;

    /**
     * Initializes the controller with the necessary dependencies.
     * 
     * This constructor performs the following operations:
     * - Initializes all service objects (config, log, session, etc.)
     * - Sets up common URL properties for use in views
     * 
     * @throws \Exception If the PHP version requirement is not met
     */
    public function __construct()
    {

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
        $this->cache = Cache::get('controller', 'file');
        $this->csrf = new CSRF();

        $this->baseUrl = $this->config->site->baseUrl ?? '';
        $this->imgUrl = $this->config->site->imgUrl ?? '';
        $this->assetsUrl = $this->baseUrl . $this->config->site->assetsUrl . '/' ?? '';
    }

    /**
     * Renders an HTML template with the provided data.
     *
     * This method merges the provided data with common URL variables and passes
     * them to the template parser for rendering. It can either return the rendered
     * content as a string or output it directly based on the return parameter.
     *
     * @param string $template Path of the template file to render.
     * @param array $data Associative array of data to be passed to the template.
     * @param bool $return When true, returns the rendered content as a string; otherwise outputs directly.
     * @return ?string Rendered template content as a string if $return is true, null otherwise.
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
     * This method instantiates model objects for database tables and attaches them
     * to the controller instance. It supports both single table and multiple table
     * initialization, as well as different database connections.
     *
     * @param array|string $table Table name or an array of table names to create models for.
     * @param string $database Database connection name as defined in configuration.
     * @return self Returns the controller instance for method chaining.
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
     * This method creates an alias for an existing model or initializes a new model
     * if it doesn't exist yet. It's useful for giving more meaningful names to models
     * or for avoiding naming conflicts.
     *
     * @param string $table Original table name of the model.
     * @param string $alias New alias name to assign to the model.
     * @param string $database Database connection name as defined in configuration.
     * @return self Returns the controller instance for method chaining.
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
