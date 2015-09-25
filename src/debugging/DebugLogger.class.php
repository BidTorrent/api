<?php

class DebugLogger extends Logger
{
	function __construct($env) {
		parent::__construct($env);

	 	ini_set("error_reporting", E_ALL);
	 	ini_set("display_errors", "On");
	}

	function log($severity, $data) {
		ob_start();
		parent::log($severity, $data);
		$data = ob_get_clean();

		$data = str_replace(" ", "&nbsp;", $data);
		$data = str_replace(array("\r\n", "\n", "\r"), "<br />", $data);

		echo "<b>[$severity]</b> $data";
	}
}

?>