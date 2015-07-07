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
		$this->log("fatal", $data);
		throw new Exception();
	}

	function log($severity, $data) {
		echo "[$severity] ";
		var_dump($data);
	}
}

?>