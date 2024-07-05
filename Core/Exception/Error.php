<?php

declare(strict_types=1);

namespace Core\Exception;

use Core\Folder\Path as Path;
use Core\Debug as Debug;
use Core\Http\Session as Session;
use Core\Config as Config;

class Error
{
    // Use readonly properties to prevent accidental modification
    private readonly Session $session;
    private readonly object $config;

    public function __construct()
    {
        $this->session = new Session();
        $config = new Config();
        $this->config = $config->get();
    }

    /**
     * Show template error
     *
     * @param int $type http response code
     * @param bool $return return template
     * @return string template
     */
    public function show(int|string $type = 404, bool $return = false): string
    {
		if(is_numeric($type))
		{
			http_response_code($type);
		}

        $template = require_once Path::VIEWS . "error/$type.php";

        // Use nullsafe operator to avoid isset check
        $error = error_get_last()?->type;
        $message = error_get_last()?->message;

        if ($error) {
            // Use match expression instead of if-else
            return match ($error) {
                // Use named arguments for clarity
                64 => $this->showFatalError(message: $message, template: $template),
                default => $this->showTemplate(template: $template, return: $return),
            };
        } else {
            return $this->showTemplate(template: $template, return: $return);
        }
    }

    // Extract common logic into separate methods
    private function showFatalError(string $message, string $template): string
    {
        echo '
            <strong>
              <font color="red">
              Fatal error captured:
              </font>
            </strong>
        ';

        $debug = new Debug();
        $debug->pre(error_get_last());

        return $template;
    }

    private function showTemplate(string|int $template, bool $return): string
    {
        if ($return) {
            return $template;
        }

        exit($template);
    }
}
