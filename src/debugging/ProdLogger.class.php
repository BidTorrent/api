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
		if ($this->isDebug()) {
			parent::log($severity, $data);
		}
		error_reporting("[$severity] $data");
	}

	function isDebug() {
		return isset($_GET['debug']);
	}
}

?>