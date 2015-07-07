<?php

class Environment {
	private $params;

	function __construct($params) {
		$this->params = $params;
	}

	function isDebug() {
		return isset($this->params["debug"]);
	}
}

?>