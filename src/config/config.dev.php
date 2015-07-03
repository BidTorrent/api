<?php

$config['log'] = new Logger();
$config['db'] = new MySql("localhost", "root", "", "bidtorrent", $config['log']);

?>