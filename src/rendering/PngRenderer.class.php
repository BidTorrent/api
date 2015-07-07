<?php

class PngRenderer {
	private $log;

	function __construct($log) {
		$this->log = $log;
	}

	function render() {
		header('Content-Type: image/png');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
		echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
		$this->log->info("render done");
	}
}

?>