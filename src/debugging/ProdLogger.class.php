<?php

class ProdLogger extends Logger
{
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
		if ($this->env->isDebug()) {
			parent::log($severity, $data);
		}

		ob_start();
		var_dump($data);		
		error_reporting("[$severity] " . ob_get_clean());
	}
}

?>