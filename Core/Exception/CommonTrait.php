<?php

declare(strict_types=1);

namespace Core\Exception;

trait CommonTrait
{	
	
	// A method to throw an exception
	public static function exception(string $message): void
	{
		throw new \Exception($message);
	}
}
