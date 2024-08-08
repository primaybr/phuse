<?php
namespace Core\Database\Drivers;

use PDO;

class MySQL implements DriversInterface {
	
	private $db;
	
	public function __construct($host, $port, $dbname, $user, $password, $options = [])
    {
        $this->connect($host, $port, $dbname, $user, $password, $options = []);
    }
	
	public function connect($host, $port, $dbname, $user, $password, $options = [])
	{
		if (empty($options)) {
            $options = [
                //PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_FOUND_ROWS => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
            ];
        }

        try {
			$this->db = new PDO("mysql:host={$host};dbname={$dbname};port={$port}", $user, $password, $options);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
	}
	
	public function getDB() {
		if ($this->db instanceof PDO) {
			return $this->db;
		}
	}
	
}