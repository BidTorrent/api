<?php

include('all.inc.php');

// FIXME: should not reference environment here
switch ($config['env'])
{
	case 'dev':
		$log = new Logger($env);
		$render = new DebugRenderer($log);

		break;

	case 'prod':
		$log = new ProdLogger($env);
		$render = new PngRenderer($log);

		break;

	default:
		die ('unknown env "' . $config['env'] . '"');

		break;
}

$db = new MySql($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name'], $log);
$impDao = new ImpressionLogDao($db);
$bidderDao = new BidderDao($db);
$rsa = new Rsa();
$bidReader = new BidInfoReader($rsa, $log);

try
{
	$tracker = new ImpressionTracking($impDao, $bidderDao, $bidReader, $log);
	$tracker->track($_GET);
}
catch (Exception $ex)
{
	$log->error($ex);
}

$render->render();	

?>
