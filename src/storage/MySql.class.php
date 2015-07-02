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
		$this->handleArrayParams($sql, $params);
		if ($params == null || (is_array($params) && count($params) == 0)) {
			$result = array();
			$queryResult = $this->connection->query($sql);
			if ($queryResult === false) {
				var_dump($this->connection->errorInfo());
				die();
			}
			foreach($queryResult as $row) {
				$result[] = $row;
			}
			return $result;
		} else {
			$statement = $this->connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));			
			$queryResult = $statement->execute($params);
			if ($queryResult === false) {
				var_dump($this->connection->errorInfo());
				die();
			}

			return $statement->fetchAll();
		}
	}

	function close() {
		$this->connection = null;
	}

	private function handleArrayParams(&$query, &$params) {
		$toRemove = array();
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				$values = implode(",", array_map(function($value) { return $this->escape($value); }, $value));
				$query = str_replace(
					":$key", 
					$values, 
					$query
				);
				$toRemove[] = $key;
			}
		}

		foreach ($toRemove as $key) {
			unset($params[$key]);
		}
	}

	private function escape($data) {
		if (is_numeric($data)) {
			return $data;
		} else {
			return $this->connection->quote($data);
		}
	}
}

?>