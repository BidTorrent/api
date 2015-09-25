<?php

class ImpressionTracking {
	private $bidderDao;
	private $bidReader;
	private $impDao;
	private $log;

	function __construct($impDao, $bidderDao, $bidReader, $log) {
		$this->bidderDao = $bidderDao;
		$this->bidReader = $bidReader;
		$this->impDao = $impDao;
		$this->log = $log;
	}

	function track($params) {
		if (!isset($params['a']))
		{
			$this->log->fatal("auction param is missing");

			return;
		}

		if (!isset($params['d']) || !is_array($params['d']))
		{
			$this->log->fatal("data param is missing");

			return;
		}

		if (!isset($params['f']) || !is_array($params['f']))
		{
			$this->log->fatal("floor param is missing");

			return;
		}

		if (!isset($params['i']) || !is_array($params['i']))
		{
			$this->log->fatal("impression param is missing");

			return;
		}

		if (!isset($params['p']))
		{
			$this->log->fatal("publisher param is missing");

			return;
		}

		$auctionId = $params['a'];
		$biddersData = $params['d'];
		$floors = $params['f'];
		$impIds = $params['i'];
		$publisher = $params["p"];

		foreach ($impIds as $i => $impId)
		{
			if (!isset ($biddersData[$i]) || !isset ($floors[$i]))
				continue;

			$bidderData = $biddersData[$i];
			$floor = $floors[$i];

			// parse the bids and check the signature
			$rsaPubKeys = $this->bidderDao->getKeys(array_keys($bidderData));
			$bids = array();

			foreach ($bidderData as $bidder => $data)
			{
				// Get bid information if bidder signature can be verified
				if (isset ($rsaPubKeys[$bidder]))
					$bid = $this->bidReader->read($data, $auctionId, $impId, $bidder, $publisher, $floor, $rsaPubKeys[$bidder]);
				else
				{
					$this->log->warning('bidder "' . $bidder . '" doesn\'t exist');

					$bid = null;
				}

				// Bidder was unknown or signature verification failed, don't log anything
				if ($bid === null)
					continue 2;

				$bids[$bidder] = $bid;
			}

			$bids['publisher'] = new BidInfo('publisher', $floor);

			// get the winner and the second one
			$first = $bids['publisher'];
			$second = $bids['publisher'];

			foreach ($bids as $bid) {
				if ($bid->price > $second->price) {
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
				$log->auctionId = $auctionId;
				$log->impId = $impId;
				$log->publisher = $publisher;
				$log->bidder = $first->bidder;
				$log->price = floor(($second->price + 0.01) * 1000000) / 1000000;
				$this->impDao->save($log);
			}
		}
	}
}

?>