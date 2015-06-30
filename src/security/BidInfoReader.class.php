<?php

class BidInfoReader {
	private $rsa;

	function __construct($rsa) {
		$this->rsa = $rsa;
	}

	function read($data, $pubKey) {
		$result = new BidInfo();
		$result->bidder = 1;
		$result->price = rand(0,2);
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