<?php

class BidderDao {
	private $db;

	function __construct($db) {
		$this->db = $db;
	}

	function getKeys($ids) {
		$result = array();

		$rows = $this->db->execute('
			SELECT
				id,
				rsaPubKey
			FROM
				bidders
			WHERE
				id IN (:ids)',
			array('ids' => $ids)
		);

		foreach($rows as $row) {
			$result[$row['id']] = $row['rsaPubKey'];
		}

		return $result;
	}	
}

?>