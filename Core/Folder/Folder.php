<?php
namespace Core\Folder;
	
class Folder{
	
	public function create($folder,$permission = 0755)
	{
		if (!file_exists($folder)) {
            mkdir($folder, $permission, true);
        } else {
            return true;
        }
	}
	
	
}
	