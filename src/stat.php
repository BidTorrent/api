<?php

include('all.inc.php');

$db = $config["db"];
$impDao = new ImpressionLogDao($db);
$view = new JsonRenderer();
$controller = new StatsController($impDao, $view);

$controller->showDailyStat(
	$_GET['publisher'],
	$_GET['from'],
	$_GET['to']);

?>