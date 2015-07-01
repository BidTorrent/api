<?php

class BidInfoReader {
	private $rsa;

	function __construct($rsa) {
		$this->rsa = $rsa;
	}

	function read($data, $bidder, $pubKey) {
		list($auction, $price, $publisher, $bidderSignature) = explode("-", $data);

		$result = new BidInfo();		
		$result->auction = $auction;
		$result->price = floatval($price);
		$result->publisher = $publisher;
		$result->bidder = $bidder;

		$bidderSignature = base64_decode($bidderSignature);
		$dataToValidate =
			number_format($result->price, 6, ".", "") .
			$result->auction .
			$result->publisher;

		if (!$this->rsa->checkSignature($dataToValidate, $bidderSignature, $pubKey)) {
			die("Bad signature $bidderSignature");
		}

		return $result;
	}
}

class BidInfo {
	public $auction;
	public $publisher;
	public $bidder;
	public $price;
}

?>