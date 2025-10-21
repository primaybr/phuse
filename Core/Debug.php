<?php

declare(strict_types=1);

namespace Core;

/**
 * Class Debug
 *
 * Provides debugging utilities for the application.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Debug {
	
	/**
	 * Dumps or prints the data based on the options provided.
	 *
	 * @param mixed $data The data to be dumped or printed.
	 * @param string|bool $options The options for dumping or printing. False for print, 'dump' for dump.
	 * @return void
	 */
	public function pre(mixed $data, string|bool $options = false): void
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
	
	/**
	 * Static version of pre() for easier usage.
	 *
	 * @param mixed $data The data to be dumped or printed.
	 * @param string|bool $options The options for dumping or printing. False for print, 'dump' for dump.
	 * @param bool $die Whether to die() after printing the data. Default is false.
	 * @return void
	 */
	public static function show(mixed $data, string|bool $options = false, bool $die = false): void
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
		
		if ($die) {
			die();
		}
	}
	
	/**
	 * Debug to browser console
	 *
	 * @param mixed $data The data to be dumped or printed.
	 * @param string $label The label for the console log.
	 * @return void
	 */
	public static function console(mixed $data, string $label = ''): void
	{
		$output = json_encode($data);
		if ($label !== '') {
			echo "<script>console.log('{$label}:', {$output});</script>";
		} else {
			echo "<script>console.log({$output});</script>";
		}
	}
}