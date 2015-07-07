<?php

class DebugRenderer {
	private $log;

	function __construct($log) {
		$this->log = $log;
	}

	function render() {
		$this->log->info("The End");
	}
}

?>
