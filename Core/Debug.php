<?php

declare(strict_types=1);

namespace Core;
	
class Debug {
	
	public function pre($data, $options = false)
	{
		echo '<pre>';
        switch ($options) {
            case 'dump':
                var_dump($data);
                break;
            default:
                print_r($data);
                break;
        }
        echo '</pre>';
	}
	
}