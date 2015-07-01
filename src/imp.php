<?php
include_once("security/Rsa.class.php");
include_once("security/BidInfoReader.class.php");
include_once("storage/MySql.class.php");
include_once("storage/ImpressionLogDao.class.php");
include_once("storage/BidderDao.class.php");
include_once("storage/PublisherDao.class.php");


// Url params
if (!isset($_GET["d"])) die("data param is missing");
$rawBids = $_GET["d"];
if (!isset($_GET["f"])) die("floor param is missing");
$floor = $_GET["f"]);
if (!isset($_GET["a"])) die("auction param is missing");
$auction = $_GET["a"]);
if (!isset($_GET["p"])) die("publisher param is missing");
$publisher = $_GET["p"]);

// Dependancies
$db = new MySql("localhost", "root", "", "bidtorrent");
$impDao = new ImpressionLogDao($db);
$bidderDao = new BidderDao($db);
$rsa = new Rsa();
$bidReader = new BidInfoReader($rsa);

// parse the bids and check the signature
$rsaPubKeys = $bidderDao->getKeys(array_keys($bids));
$bids = array();
foreach ($rawBids as $bidder => $signedBid) {
	$bids[] = $bidReader->read($signedBid, $auction, $bidder, $publisher, $floor, $rsaPubKeys[$bidder]);
}


$log = new ImpressionLog();
foreach ($bids as $bidder => $signedBid) {
	$bid = $bidReader->read($signedBid, $bidder, $rsaPubKeys[$bidder]);

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
$impDao->save($log);

print_r($log);

?>