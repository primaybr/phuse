<?php

declare(strict_types=1);

namespace Core\Http;

// Use final class to prevent inheritance
final class Session
{
	
	public array $session;
	
	public function __construct()
	{
		$this->session = $_SESSION;
	}
	
	public function check(string $key): bool
	{
		if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
            return true;
        }
		else{
			return false;
		}
	}
	
	public function set(string $key, mixed $value): bool
    {
        if (!empty($key) && !empty($value)) {
            $_SESSION[$key] = $value;
			return true;
        } else {
            return false;
        }
    }
	
	public function get(string $key = ''): mixed
    {
        if (!empty($key)) {
			if(isset($_SESSION[$key]))
			{
				return $_SESSION[$key];
			}
            return '';
        }
		else
		{
			return $_SESSION;
		}
    }
	
	public function flash(string $key): mixed
	{
		$data = $this->get($key);
		unset($_SESSION[$key]);
		return $data;
	}
	
	public function destroy(): void
	{
		session_unset();
		session_destroy();
	}
}