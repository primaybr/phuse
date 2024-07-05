<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Folder\Folder as Folder;

class Cache implements CacheInterface
{
    use CacheTrait;

    protected Folder $folder;

	public function __construct()
	{
		$this->folder = new Folder();
	}
}