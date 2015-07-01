<?php

class BidderDao {
	private $db;

	function __construct($db) {
		$this->db = $db;
	}

	function getKeys($ids) {
		$result = array();
		$ids = array(1,2,3);
		foreach ($ids as $id) {
			$result[$id] = file_get_contents("security/testKeys/key-${id}-public.pem");
		}
		return $result;
	}	
}

?>