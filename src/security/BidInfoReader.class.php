<?php

// - Parses raw data from the request
// - Verify the bidder signature
class BidInfoReader
{
	private $rsa;
	private $log;

	function __construct($rsa, $log)
	{
		$this->rsa = $rsa;
		$this->log = $log;
	}

	function read($data, $auctionId, $impId, $bidder, $publisher, $floor, $pubKey)
	{
		$floor = floatval($floor);
		$now = time();
		$remote = isset ($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

		// Decode fields from signed data buffer
		$fields = explode('-', $data);

		switch (count ($fields))
		{
			case 2:
				list ($price, $signature) = $fields;

				$message = number_format($price, 6, '.', '') . '|' . $auctionId . '|' . $impId . '|' . $publisher . '|' . number_format($floor, 6, '.', '');

				$address = $remote;
				$timestamp = $now;

				break;

			case 5:
				list ($version, $signature, $price, $timestamp, $address) = $fields;

				$message = implode('|', array($version, number_format($price, 6, '.', ''), $timestamp, $address, $auctionId, $impId, $publisher, number_format($floor, 6, '.', '')));

				break;

			default:
				$this->log->fatal('Unrecognized signed data: ' . $data);

				return null;
		}

		// Reject invalid IP address
		if ($address !== $remote)
		{
			$this->log->fatal('Invalid IP address on message: ' . $message . ', remote: ' . $remote);

			return null;
		}

		// Reject invalid timestamp
		if (abs($now - $timestamp) > 300)
		{
			$this->log->fatal('Outdated message: ' . $message . ', timestamp: ' . $timestamp);

			return null;
		}

		// Reject invalid signature
		if (!$this->rsa->checkSignature($message, base64_decode($signature), $pubKey))
		{
			$this->log->fatal('Corrupted signed message: ' . $message . ', signature: ' . $signature);

			return null;
		}

		return new BidInfo($bidder, floatval($price));
	}
}

class BidInfo
{
	public $bidder;
	public $price;

	function __construct($bidder, $price)
	{
		$this->bidder = $bidder;
		$this->price = $price;
	}
}

?>