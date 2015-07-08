<?php

class Logger
{
	var $env;

	function __construct($env) {
		$this->env = $env;
	}

	function info($data) {
		$this->log("info", $data);
	}

	function warning($data) {
		$this->log("warning", $data);
	}

	function error($data) {
		$this->log("error", $data);		
	}

	function fatal($data) {
		throw new Exception($data);
	}

	function log($severity, $data) {
		if (is_object($data) || is_array($data))
			var_dump($data);
		else
			echo $data;
		debug_backtrace();
	}
}


function bd_error_handler($errno , $errstr, $errfile, $errline, $errcontext) 
{
	global $config;
	$log = $config['log'];

	$severity = "";
	switch($errno) {
		case E_ERROR:
		case E_RECOVERABLE_ERROR:
		case E_USER_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_PARSE:
			$log->error($errstr);
		break;
		case E_WARNING:
		case E_CORE_WARNING:
		case E_USER_WARNING:
		case E_COMPILE_WARNING:
			$log->warning($errstr);
		break;
		default:
			$log->info($errstr);
		break;
	}
}

set_error_handler("bd_error_handler");

?>