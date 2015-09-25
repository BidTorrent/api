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
				impId,
				price
			)
			VALUES 
			(
				:date,
				:publisher,
				:bidder,
				:auctionId,
				:impId,
				:price
			)',
			array(				
				'date' => $log->date,
				'publisher' => $log->publisher,
				'bidder' => $log->bidder,
				'auctionId' => $log->auctionId,
				'impId' => $log->impId,
				'price' => $log->price
			)
		);
	}
}

// Represents an impression log
class ImpressionLog {
	public $date;
	public $auctionId;
	public $impId;
	public $publisher;
	public $bidder;
	public $price;
}

?>