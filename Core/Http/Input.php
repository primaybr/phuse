<?php

declare(strict_types=1);

namespace Core\Http;

class Input
{
	public function get(string $name = ''): string|array
	{
		if ($name) {
            return $_GET[$name] ?? '';
        }

        return $_GET;
	}
	
	public function post(string $name = ''): string|array
	{
		if($name) {
			return $_POST[$name] ?? '';
		}
		
		return $_POST;
	}
	
	public function put(): mixed
	{
		return file_get_contents("php://input");
	}
	
	public function delete(string $name = ''): string|array
	{
		parse_str(file_get_contents("php://input"),$_DELETE);

		if($name) {
			return $_DELETE[$name] ?? '';
		}
		
		return $_DELETE;
	}
	
}