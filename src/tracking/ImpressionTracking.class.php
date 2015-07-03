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
		$bids['publisher'] = new BidInfo('publisher', $floor);

		// get the winner and the second one
		$first = $bids['publisher'];
		$second = $bids['publisher'];
		foreach ($bids as $bid) {
			if($bid->price > $second->price) {
				if ($bid->price > $first->price) {
					$second = $first;
					$first = $bid;
				} else {
					$second = $bid;
				}
			}
		}

		// log the impression with second's price + 0.01
		if ($first->bidder != 'publisher') {
			$log = new ImpressionLog();
			$log->date = time();
			$log->auction = $auction;
			$log->publisher = $publisher;
			$log->bidder = $first->bidder;
			$log->price = floor(($second->price + 0.01) * 1000000) / 1000000;
			$this->impDao->save($log);
		}
	}
}

?>