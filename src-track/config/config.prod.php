<?php

$config['log'] = new ProdLogger($env);
$config['db'] = new MySql("localhost", "bidtorrent", "hack@thon", "bidtorrent", $config['log']);

?>