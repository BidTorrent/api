<?php
include_once("security/Rsa.class.php");
include_once("security/BidInfoReader.class.php");
include_once("storage/MySql.class.php");
include_once("storage/ImpressionLogDao.class.php");
include_once("storage/BidderDao.class.php");
include_once("storage/PublisherDao.class.php");
include_once("tracking/ImpressionTracking.class.php");


// Dependancies
$db = new MySql("localhost", "root", "", "bidtorrent");
$impDao = new ImpressionLogDao($db);
$bidderDao = new BidderDao($db);
$rsa = new Rsa();
$bidReader = new BidInfoReader($rsa);

// go
$tracker = new ImpressionTracking($impDao, $bidderDao, $bidReader);
$tracker->track($_GET);
$db->close();

//
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');

?>