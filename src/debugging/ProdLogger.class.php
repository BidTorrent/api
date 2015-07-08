<?php

class ProdLogger extends Logger
{
	private $counter;

	function __construct($env) {
		parent::__construct($env);

	 	ini_set("error_reporting", "E_ALL");
		if ($this->env->isDebug()) {
		 	ini_set("display_errors", "On");
	 	} else {
		 	ini_set("display_errors", "Off");	 		
	 	}
	}

	function log($severity, $data) {
		ob_start();
		parent::log($severity, $data);
		$data = ob_get_clean();

		if ($this->env->isDebug()) {
			$header = "X-$severity-" . ++$this->counter . ": " . str_replace(array("\r\n", "\n", "\r"), ". ", $data);
			header(substr($header, 0, 250));
		}
		
		error_log("[$severity] $data");
	}
}

?>