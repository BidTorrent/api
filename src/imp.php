<?php

include_once("debugging/Environment.class.php");
include_once("debugging/Logger.class.php");
include_once("debugging/DebugLogger.class.php");
include_once("debugging/ProdLogger.class.php");
include_once("security/Rsa.class.php");
include_once("security/BidInfoReader.class.php");
include_once("storage/MySql.class.php");
include_once("storage/ImpressionLogDao.class.php");
include_once("storage/BidderDao.class.php");
include_once("tracking/ImpressionTracking.class.php");
include_once("rendering/PngRenderer.class.php");
include_once("rendering/DebugRenderer.class.php");

$env = new Environment($_GET);
if (!file_exists('config/config.php')) die('config/config.php is not found, copy the appropriate "config/config.XXX.php" files to "config/config.php"');
$config = array();
include_once("config/config.php");

// Dependancies
$log = $config["log"];
$db = $config["db"];
$impDao = new ImpressionLogDao($db);
$bidderDao = new BidderDao($db);
$rsa = new Rsa();
$bidReader = new BidInfoReader($rsa, $log);
$render = $config['renderer'];

try
{
	$tracker = new ImpressionTracking($impDao, $bidderDao, $bidReader, $log);
	$tracker->track($_GET);
}
catch(Exception $ex) {
	$log->error($ex);
}

$render->render();	

?>