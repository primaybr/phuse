<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Config as Config;
use Core\Http\Session;

class Request
{
    private string $method;
    private mixed $data;
    private string $wrapper;
    private array $options = [];
    private array $ssl = [];
    private array $finalOptions = [];
    private mixed $stream;
    private $config;
    private string $baseUrl;
    private string $adminUrl;
    private string $apiExternalUrl;
    private string $env;
    private bool $useRefresh;
    public int|string $httpResponseCode = 200;
    public string $httpResponseStatus;
    public Session $session;
    private object $token;

    public function __construct(string $wrapper = 'http')
    {
        $this->setWrapper($wrapper);
        $this->config = (new Config())->get();
        $this->baseUrl = $this->config->site->baseUrl;
        $this->apiExternalUrl = isset($this->config->apiExternal->baseUrl) ? $this->config->apiExternal->baseUrl : '';
        $this->useRefresh = isset($this->config->apiExternal->useRefresh) ? (bool)($this->config->apiExternal->useRefresh === 'true') : false;
        $this->env = $this->config->env;
        $this->session = new Session;
        $this->adminUrl = $this->config->site->adminUrl;
    }

    // Use union types for parameters and return types
    public function request(string $method, string $url, array|string $data = [], bool $refresh = true): self
    {
        if ($data) {
            $this->data = $this->setContent($data);
        }

        $this->method = strtoupper($method);

        $this->getContextOptions();

        $context = stream_context_create($this->finalOptions);

        // Use nullsafe operator to avoid nested null checks
        if ($this->env !== 'local') {
            $url = str_replace($this->baseUrl, "http://localhost/", $url);
        }

        $this->stream = fopen($url, 'r', false, $context);
        $this->httpResponseCode = isset($http_response_header[0]) ? substr($http_response_header[0], 9, 3) : 200;

        if ($refresh && $this->useRefresh && $this->httpResponseCode == 401) {
            return $this->refreshRequest($method, $url, $data);
        }
        return $this;
    }

    public function getContent(): string|false
    {
        
        if (is_resource($this->stream)) {
            $content = stream_get_contents($this->stream);
            fclose($this->stream);
            return $content;
        } else {
            return false;
        }
    }

    private function refreshRequest(string $method, string $url, array|string $data = []): self
    {
        $token = $this->session->get('sesstoken');
        if (!$token) {
            return $this;
        }
        $this->token = json_decode($token);

        // refresh token
        $newToken = $this->setContentType('application/json')
            ->setHeader("Authorization: Bearer " . $this->token->access_token)
            ->request('POST', $this->apiExternalUrl . '/auth/refresh', ['json' => json_encode(['refresh_token' => $this->token->refresh_token])], false)
            ->getContent();
            
        if ($this->httpResponseCode == 401) {
            return $this;
        }
        
        $token = json_decode($newToken);
        
        $this->setHeader("Authorization: Bearer " . $token->access_token);
        list($header, $payload, $signature) = explode('.', $token->id_token);

        $jsonToken = base64_decode($payload);
        $arrayToken = json_decode($jsonToken, true);

        $this->session->set('sessdata', $arrayToken['dat']);
        $this->session->set('sesstoken', $newToken);

        // resend request
        if ($data) {
            $this->data = $this->setContent($data);
        }
        $this->method = strtoupper($method);

        $this->getContextOptions();
        $context = stream_context_create($this->finalOptions);
        // Use nullsafe operator to avoid nested null checks
        if ($this->env !== 'local') {
            $url = str_replace($this->baseUrl, "http://localhost/", $url);
        }

        $this->stream = fopen($url, 'r', false, $context);
        $this->httpResponseCode = isset($http_response_header[0]) ? substr($http_response_header[0], 9, 3) : 200;

        return $this;
    }

    public function setHeader(string $header): self
    {
        $this->options['header'] = $header;

        return $this;
    }

    public function setContent(array|string $content): self
    {
        // Use match expression instead of if-else
        $this->options['content'] = match (true) {
            array_key_exists('json', $content) => $content['json'],
            is_array($content) => http_build_query($content),
            is_string($content) => $content,
            default => throw new \InvalidArgumentException('Invalid content type'),
        };

        return $this;
    }

    public function setContentType(string $type = 'application/x-www-form-urlencoded'): self
    {
        // Use null coalescing operator to avoid isset check
        $this->options['content_type'] ??= $type;

        return $this;
    }

    public function setWrapper(string $wrapper = 'http'): self
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    public function setSSL(bool $on = true): self
    {
        if (!$on) {
            $this->ssl = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
        }

        return $this;
    }

    public function setReferer(string $referer): self
    {
        $this->options['referer'] = $referer;

        return $this;
    }

    public function setOptions(string $wrapper, mixed $value): self
    {
        $this->options[$wrapper] = $value;

        return $this;
    }

    private function getContextOptions(): self
    {
        $this->options['max_redirects'] = 0;
        $this->options['ignore_errors'] = 1;
        $this->options['method'] = $this->method;

        if ($this->method === 'POST' || $this->method === 'PUT') {

            if (empty($this->data)) {
                die('You need to specify content to use POST request.');
            }

            $this->setContentType();

            $this->setHeader(
                "Content-type: " . $this->options['content_type'] . " \r\n" .
                "Content-Length: " . strlen($this->options['content']) . "\r\n" .
                (isset($this->options['header']) ? $this->options['header'] . "\r\n" : "") .
                (isset($this->options['referer']) ? "Referer: " . $this->options['referer'] . "\r\n" : "") .
                (isset($this->options['uid']) ? "uid: " . $this->options['uid'] . "\r\n" : "") .
                (isset($this->options['token']) ? "token: " . $this->options['token'] . "\r\n" : "") .
                (isset($this->options['Auth']) ? "Auth: " . $this->options['Auth'] . "\r\n" : "")
            );
        } else {
            $this->setHeader(
                (isset($this->options['content_type']) ? "Content-type: " . $this->options['content_type'] . " \r\n" : "") .
                (isset($this->options['header']) ? $this->options['header'] . "\r\n" : "")
            );
        }


        foreach ($this->options as $key => $val) {
            $this->finalOptions[$this->wrapper][$key] = $val;
        }

        $this->finalOptions = array_merge($this->finalOptions, $this->ssl);

        return $this;
    }

    // Add getters and setters for private properties
    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getWrapper(): string
    {
        return $this->wrapper;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSsl(): array
    {
        return $this->ssl;
    }

    public function getFinalOptions(): array
    {
        return $this->finalOptions;
    }

    public function getStream(): mixed
    {
        return $this->stream;
    }

    public function setStream(mixed $stream): self
    {
        $this->stream = $stream;
        return $this;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function setConfig(Config $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    public function setEnv(string $env): self
    {
        $this->env = $env;
        return $this;
    }

    public function getHttpResponseCode(): mixed
    {
        return $this->httpResponseCode;
    }

    public function setHttpResponseCode(int $httpResponseCode): self
    {
        $this->httpResponseCode = $httpResponseCode;
        $this->httpResponseStatus = (new Response($httpResponseCode))->statusName;
        return $this;
    }
}
