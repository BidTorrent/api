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

	function track($rawParameters) {
		if (!isset($rawParameters["d"])) die("data param is missing");
		$rawBids = $rawParameters["d"];
		if (!isset($rawParameters["f"])) die("floor param is missing");
		$floor = $rawParameters["f"]);
		if (!isset($rawParameters["a"])) die("auction param is missing");
		$auction = $rawParameters["a"]);
		if (!isset($rawParameters["p"])) die("publisher param is missing");
		$publisher = $rawParameters["p"]);

		// parse the bids and check the signature
		$rsaPubKeys = $this->bidderDao->getKeys(array_keys($bids));
		$bids = array();
		foreach ($rawBids as $bidder => $signedBid) {
			$bids[] = $this->bidReader->read($signedBid, $auction, $bidder, $publisher, $floor, $rsaPubKeys[$bidder]);
		}

		$log = new ImpressionLog();
		foreach ($bids as $bidder => $signedBid) {
			$bid = $this->bidReader->read($signedBid, $bidder, $rsaPubKeys[$bidder]);

			// ensures that all the bids are about the same auction/publisher/bidder tuple
			if ($log->date != null) {
				if ($bid->publisher != $log->publisher) die("Wrong publisher");
				if ($bid->auction != $log->auction) die("Wrong auction");
			}

			// saves the highest price
			if ($log->date == null || $bid->price > $log->price) {
				$log->date = time();
				$log->auction = $bid->auction;
				$log->publisher = $bid->publisher;
				$log->bidder = $bid->bidder;
				$log->price = $bid->price;
			}
		}
		$this->impDao->save($log);
		print_r($log);
	}
}

?>