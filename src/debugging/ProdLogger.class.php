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
		ob_start();
		var_dump($data);		
		$data = ob_get_clean();
		
		if ($this->env->isDebug()) {
			header($severity, $data);
		} else {
			error_reporting("[$severity] $data");
		}
	}
}

?>