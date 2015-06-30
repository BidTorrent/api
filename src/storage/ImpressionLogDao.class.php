<?php

class ImpressionLogDao {
	private $db;

	function __construct($db) {
		$this->db = $db;
	}

	// $log is of type ImpressionLog
	function Save($log) {

	}
}

// Represents an impression log
class ImpressionLog {
	public $date;
	public $auction;
	public $publisher;
	public $bidder;
	public $price;
}

?>