<?php

$config['log'] = new ProdLogger();
$config['db'] = new MySql("localhost", "bidtorrent", "hack@thon", "bidtorrent", $config['log']);

?>