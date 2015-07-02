<?php

// - Parses raw data from the request
// - Verify the bidder signature
class BidInfoReader {
	private $rsa;

	function __construct($rsa) {
		$this->rsa = $rsa;
	}

	function read($data, $auction, $bidder, $publisher, $floor, $pubKey) {
		list($price, $bidderSignature) = explode("-", $data);
		$floor = floatval($floor);

		$result = new BidInfo();
		$result->price = floatval($price);
		$result->bidder = $bidder;

		$bidderSignature = base64_decode($bidderSignature);
		$dataToValidate =
			number_format($result->price, 6, ".", "") .
			$auction .
			$publisher .
			number_format($floor, 6, ".", "");

		if (!$this->rsa->checkSignature($dataToValidate, $bidderSignature, $pubKey)) {
			die("Bad signature $bidderSignature");
		}

		return $result;
	}
}

class BidInfo {
	public $bidder;
	public $price;
}

?>