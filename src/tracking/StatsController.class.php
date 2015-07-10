<?php

class StatsController {
	private $dao;
	private $view;

	function __construct($dao, $view) {
		$this->dao = $dao;
		$this->view = $view;
	}

	function showDailyStat($publisher, $from, $to) {
		$data = $this->dao->getPublisherDailyStats($publisher, $from, $to);
		$this->view->render($data);
	}
}

?>