<?php

include('all.inc.php');

$db = $config["db"];
$impDao = new ImpressionLogDao($db);

var_dump($impDao->getPublisherDailyStats(
	3, 
	1435708800,
	1436486400
));

?>