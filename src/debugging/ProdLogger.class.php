<?php

class ProdLogger extends Logger
{
	private $counter;

	function __construct($env) {
		parent::__construct($env);
	}

	function log($severity, $data) {
		ob_start();
		parent::log($severity, $data);
		$data = ob_get_clean();

		if ($this->env->isDebug()) {
			$header = "X-$severity-" . ++$this->counter . ": " . str_replace(array("\r\n", "\n", "\r"), ". ", $data);
			header(substr($header, 0, 127));
		}
		
		if ($severity != "debug") {
			error_log("[$severity] $data");
		}
	}
}

?>