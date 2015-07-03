<?php

class Logger
{
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
		die();
	}

	function log($severity, $data) {
		echo "[$severity] ";
		var_dump($data);
	}
}

?>