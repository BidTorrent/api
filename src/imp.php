<?php

include('all.inc.php');

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