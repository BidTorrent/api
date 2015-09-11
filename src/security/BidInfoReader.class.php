<?php

// - Parses raw data from the request
// - Verify the bidder signature
class BidInfoReader {
	private $rsa;
	private $log;

	function __construct($rsa, $log) {
		$this->rsa = $rsa;
		$this->log = $log;
	}

	function read($data, $auctionId, $impId, $bidder, $publisher, $floor, $pubKey) {
		list($price, $signature) = explode('-', $data);

		$floor = floatval($floor);
		$result = new BidInfo($bidder, floatval($price));
		$dataToValidate = number_format($result->price, 6, '.', '') . '|' . $auctionId . '|' . $impId . '|' . $publisher . '|' . number_format($floor, 6, '.', '');
		$signature = base64_decode($signature);

		if (!$this->rsa->checkSignature($dataToValidate, $signature, $pubKey)) {
			$this->log->fatal(
				"Bad signature [" . base64_encode($signature) . "\n" .
				"The concatenated parameters were [$dataToValidate]\n" .
				"The bidder public key was [$pubKey]\n" .
				"Price: {$result->price}, Auction: {$auctionId}, Impression: {$impId}, Publisher: {$publisher}, Floor:{$floor}\n"
			);
		}

		return $result;
	}
}

class BidInfo {
	public $bidder;
	public $price;

	function __construct($bidder, $price) {
		$this->bidder = $bidder;
		$this->price = $price;
	}
}

?>