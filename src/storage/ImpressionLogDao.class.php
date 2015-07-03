<?php

class ImpressionLogDao {
	private $db;

	function __construct($db) {
		$this->db = $db;
	}

	function save($log) {
		$this->db->execute('
			INSERT INTO `log_impressions` (
				date,
				publisherId,
				bidderId,
				auctionId,
				price
			)
			VALUES 
			(
				:date,
				:publisher,
				:bidder,
				:auction,
				:price
			)',
			array(				
				'date' => $log->date,
				'publisher' => $log->publisher,
				'bidder' => $log->bidder,
				'auction' => $log->auction,
				'price' => $log->price
			)
		);
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