<?php

class MySql {
	private $connection;

	function __construct($host, $user, $pass, $db) {
		$this->connection = new PDO (
			"mysql:dbname=$db;host=$host",
			$user,
			$pass
		);
	}

	function execute($sql, $params = null) {
		if ($params == null) {
			$result = array();
			foreach($this->connection->query($sql) as $row) {
				$result[] = $row;
			}
			return $result;
		} else {
			$statement = $this->connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$statement->execute($params);
			return $statement->fetchAll();
		}
	}
	
	function close() {
		$this->connection->close();
	}
}

?>