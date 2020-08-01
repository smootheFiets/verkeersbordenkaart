<?php

require_once('config.cfg.php');

//connect met database, dit gaat mis bij uitvoering van setup/install.php, maar wordt daar ondervangen
$db['link'] = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']);
//stel karakterset in voor mysqli_real_escape_string
mysqli_set_charset($db['link'], 'utf8');

?>