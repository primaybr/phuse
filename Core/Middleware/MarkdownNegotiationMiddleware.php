<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Http\MarkdownNegotiation;
use Core\Http\MarkdownResponseTransformer;

/**
 * Converts eligible public HTML responses when an agent requests Markdown.
 */
final class MarkdownNegotiationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly MarkdownResponseTransformer $transformer = new MarkdownResponseTransformer()
    ) {
    }

    public function process(callable $next): mixed
    {
        if (!MarkdownNegotiation::isRequested($_SERVER)) {
            return $next();
        }

        $bufferLevel = ob_get_level();
        $completed = false;
        ob_start();

        register_shutdown_function(function () use (&$completed, $bufferLevel): void {
            if ($completed || ob_get_level() <= $bufferLevel) {
                return;
            }

            $body = (string) ob_get_clean();
            $this->emit($body);
            $completed = true;
        });

        try {
            $result = $next();
            $body = (string) ob_get_clean();
        } catch (\Throwable $exception) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            throw $exception;
        }

        $completed = true;
        $this->emit($body);
        return $result;
    }

    private function emit(string $body): void
    {
        $status = http_response_code();
        $status = is_int($status) && $status >= 100 ? $status : 200;

        try {
            $response = $this->transformer->transform($body, $status, headers_list());
        } catch (\Throwable) {
            echo $body;
            return;
        }

        if ($response === null) {
            echo $body;
            return;
        }

        if (!headers_sent()) {
            foreach ($response['remove'] as $name) {
                header_remove($name);
            }
            foreach ($response['headers'] as $name => $value) {
                header($name . ': ' . $value, true);
            }
        }

        echo $response['body'];
    }
}
