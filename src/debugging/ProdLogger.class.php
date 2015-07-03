<?php

class ProdLogger extends Logger
{	
	function __construct() {
		if ($this->isDebug()) {
		 	ini_set("error_reporting", "E_ALL");
		 	ini_set("display_errors", "On");
	 	}
	}

	function log($severity, $data) {
		ob_start();
		parent::log($severity, $data);
		$result = ob_get_clean();
		error_reporting($result);

		if ($this->isDebug()) {
			echo $result;
		}
	}

	function isDebug() {
		return isset($_GET['debug']);
	}
}

?>