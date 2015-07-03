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

	function read($data, $auction, $bidder, $publisher, $floor, $pubKey) {
		list($price, $bidderSignature) = explode("-", $data);
		$floor = floatval($floor);

		$result = new BidInfo($bidder, floatval($price));
		$bidderSignature = base64_decode($bidderSignature);
		$dataToValidate =
			number_format($result->price, 6, ".", "") .
			$auction .
			$publisher .
			number_format($floor, 6, ".", "");

		if (!$this->rsa->checkSignature($dataToValidate, $bidderSignature, $pubKey)) {
			$this->log->fatal("Bad signature $bidderSignature");
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