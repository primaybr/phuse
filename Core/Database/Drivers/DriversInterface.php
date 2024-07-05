<?php
namespace Core\Database\Drivers;
	
	interface DriversInterface {
		
		public function connect($host, $port, $dbname, $user, $password, $options = []);
				
		public function getDB();
	}
	