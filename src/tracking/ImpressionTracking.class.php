<?php

class ImpressionTracking {
	private $impDao;
	private $bidderDao;
	private $bidReader;

	function __construct($impDao, $bidderDao, $bidReader) {
		$this->impDao = $impDao;
		$this->bidderDao = $bidderDao;
		$this->bidReader = $bidReader;
	}

	function track($params) {
		if (!isset($params["d"])) die("data param is missing");
		$rawBids = $params["d"];
		if (!isset($params["f"])) die("floor param is missing");
		$floor = $params["f"];
		if (!isset($params["a"])) die("auction param is missing");
		$auction = $params["a"];
		if (!isset($params["p"])) die("publisher param is missing");
		$publisher = $params["p"];

		// parse the bids and check the signature
		$rsaPubKeys = $this->bidderDao->getKeys(array_keys($rawBids));
		$bids = array();
		foreach ($rawBids as $bidder => $signedBid) {
			$bids[$bidder] = $this->bidReader->read($signedBid, $auction, $bidder, $publisher, $floor, $rsaPubKeys[$bidder]);
		}

		$log = new ImpressionLog();
		foreach ($bids as $bidder => $bid) {
			
			// ensures that all the bids are about the same auction/publisher/bidder tuple
			if ($log->date != null) {
				if ($bid->publisher != $log->publisher) die("Wrong publisher");
				if ($bid->auction != $log->auction) die("Wrong auction");
			}

			// saves the highest price
			if ($log->date == null || $bid->price > $log->price) {
				$log->date = time();
				$log->auction = $auction;
				$log->publisher = $publisher;
				$log->bidder = $bidder;
				$log->price = $bid->price;
			}
		}
		$this->impDao->save($log);
		print_r($log);
	}
}

?>