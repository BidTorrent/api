<?php
include_once("security/Rsa.class.php");
include_once("security/BidInfoReader.class.php");
include_once("storage/MySql.class.php");
include_once("storage/ImpressionLogDao.class.php");
include_once("storage/BidderDao.class.php");
include_once("storage/PublisherDao.class.php");


// url params
if (!isset($_GET["d"])) die("plop");
$bids = $_GET["d"];

// dependancies
$db = new MySql("localhost", "root", "");
$impDao = new ImpressionLogDao($db);
$bidderDao = new BidderDao($db);
$rsa = new Rsa();
$bidReader = new BidInfoReader($rsa);

// meat
$rsaPubKeys = $bidderDao->getKeys(array_keys($bids));
$log = new ImpressionLog();

foreach ($bids as $bidder => $signedBid) {
	$bid = $bidReader->read($signedBid, $rsaPubKeys[$bidder]);
	
	// ensures that all the bids are about the same auction/publisher/bidder tuple
	if ($log->date != null) {
		if ($bid->publisher != $log->publisher) die("Wrong publisher");
		if ($bid->bidder != $log->bidder ) die("Wrong bidder");
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
$log->price += 0.01;

$impDao->save($log);

print_r($log);

?>