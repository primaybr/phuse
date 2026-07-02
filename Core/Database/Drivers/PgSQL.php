<?php
namespace Core\Database\Drivers;

use PDO;

class PgSQL implements DriversInterface {

	private $db;

	public function __construct($host, $port, $dbname, $user, $password, $options = [])
    {
        $this->connect($host, $port, $dbname, $user, $password, $options = []);
    }

	public function connect($host, $port, $dbname, $user, $password, $options = []){
		if (empty($options)) {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];
        }

        // Let PDOException propagate - swallowing it here left $db null with no signal
        // to the caller, so every downstream layer (Connection, ConnectionPool, Model)
        // ended up assuming a valid PDO handle existed when it didn't.
        $this->db = new PDO("pgsql:host={$host};dbname={$dbname};port={$port}", $user, $password, $options);
	}
	
	public function getDB() {
		if ($this->db instanceof PDO) {
			return $this->db;
		}
	}
	
	
}