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

	function getPublisherDailyStats($publisher, $from, $to) {
		$result = array();
		$rows = $this->db->execute('
			SELECT
				DATE(FROM_UNIXTIME(`date`)) AS `date`,
				count(id) AS impressions,
				SUM(price) AS revenue
			FROM
				log_impressions
			WHERE 
				`date` >= :from
				AND `date` < :to
				AND publisherId = :publisher
			GROUP BY
				DATE(FROM_UNIXTIME(`date`))
			',
			array(				
				'publisher' => $publisher,
				'from' => $from,
				'to' => $to
			)
		);

		foreach ($rows as $row) {
			$object = new PublisherDailyStat();
			$object->date = $row['date'];
			$object->impressions = (int) $row['impressions'];
			$object->revenue = (float) $row['revenue'];
			$result[] = $object;
		}

		return $result;
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

// Represents a daily stat for a publisher
class PublisherDailyStat {
	public $date;
	public $impressions;
	public $revenue; 
}

?>