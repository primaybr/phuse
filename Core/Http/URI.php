<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Config as Config;

// Use final class to prevent inheritance
final class URI
{
    public const REPLACE = '-';
    public const PATTERN = [
        '&\#\d+?;' => '',
        '&\S+?;' => '',
        '\s+' => self::REPLACE,
        '[^a-z0-9\-\._]' => '',
        self::REPLACE.'+' => self::REPLACE,
        self::REPLACE.'$' => self::REPLACE,
        '^'.self::REPLACE => self::REPLACE,
        '\.+$' => '',
    ];

    public function makeURL(string $string): string
    {
        $url = strip_tags($string);

        foreach (self::PATTERN as $key => $val) {
            $url = preg_replace('#'.$key.'#i', $val, $url);
        }

        $url = html_entity_decode($url);
        $url = trim($url);
        $url = stripslashes($url);
        $url = str_replace([',', '.'], ['', ''], $url);
        $url = strtolower($url);

        return $url;
    }

    public function makeFullURL(string $string): string
    {
        $url = $this->makeURL($string);
        $config = new Config;

        return $config->get()->site->baseUrl.$url;
    }

    public function makeImagePath(string $image, string $size): string
    {
        $config = new Config;

        if (str_contains($image, 'place-hold.it')) {
            return $image;
        }

        $basename = basename($image);
        return $config->get()->site->baseUrl.str_replace($basename, $size.'/'.$basename, $image);
    }

    public function makeImageYoutube(string $url, int $type = 0): string|false
    {
        $code = $this->getYoutubeCode($url);
        if (!empty($code)) {
            return "https://img.youtube.com/vi/{$code}/{$type}.jpg";
        }

        return false;
    }

    public function getYoutubeCode(string $url): string|false
    {
        $parts = parse_url($url);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $qs);
            if (isset($qs['v'])) {
                return $qs['v'];
            }
            if (isset($qs['vi'])) {
                return $qs['vi'];
            }
        }
        if (isset($parts['path'])) {
            $path = explode('/', trim($parts['path'], '/'));
            return $path[array_key_last($path)];
        }
        return false;
    }

    // Use string return type and null coalescing operator
    public function getCurrentURL(bool $full = false): string
    {
        if ($full) {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    // Use string return type and null coalescing operator
    public function getHttpReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    // Use string return type and null coalescing operator
    public function getSegment(int $segment): string
    {
        $config = new Config;
        $config = $config->get();

        $serverName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
        $uriPath = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
        $uriPath = $uriPath.'://'.$serverName.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($config->site->baseUrl, '', $uriPath);

        $uriSegments = explode('/', parse_url($uriPath, PHP_URL_PATH));

        return $uriSegments[$segment] ?? '';
    }
	
	public function getProtocol(): string
	{
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || 
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') 
		{
			return 'https://';
		}
		else {
			return 'http://';
		}
	}
	
	public function getHost() 
	{
		$possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
		$sourceTransformations = array(
			"HTTP_X_FORWARDED_HOST" => function($value) {
				$elements = explode(',', $value);
				return trim(end($elements));
			}
		);
		$host = '';
		foreach ($possibleHostSources as $source)
		{
			if (!empty($host)) break;
			if (empty($_SERVER[$source])) continue;
			$host = $_SERVER[$source];
			if (array_key_exists($source, $sourceTransformations))
			{
				$host = $sourceTransformations[$source]($host);
			} 
		}

		// Remove port number from host
		$host = preg_replace('/:\d+$/', '', $host);

		return trim($host);
	}

    // Use void return type
    public function redirect(string $url): void
    {
        header('Location: '.$url, true, 302);
        die();
    }

    // Use string return type
    public function normalizeURL(string $url): string
    {
        $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url);

        return $url;
    }
}
