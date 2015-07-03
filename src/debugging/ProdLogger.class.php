<?php

class ProdLogger extends Logger
{
	function log($severity, $data) {
		ob_start();
		parent::log($severity, $data);
		$result = ob_get_clean();
		error_reporting($result);
	}
}

?>