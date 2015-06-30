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
		$result->publisher = $publisher
		$result->bidder = $bidder;
		return $result;
	}
}

class BidInfo {
	public $date;
	public $auction;
	public $publisher;
	public $bidder;
	public $price;
}

?>