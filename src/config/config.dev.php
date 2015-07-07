<?php

$config['log'] = new Logger($env);
$config['db'] = new MySql("localhost", "root", "", "bidtorrent", $config['log']);
$config['renderer'] = new DebugRenderer($config['log']);

?>