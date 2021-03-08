<?php
/*
This file is part of Verkeersbordenkaart
Copyright (C) 2020 Jasper Vries

Verkeersbordenkaart is free software: you can redistribute it and/or 
modify it under the terms of version 3 of the GNU General Public 
License as published by the Free Software Foundation.

Verkeersbordenkaart is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Verkeersbordenkaart. If not, see <http://www.gnu.org/licenses/>.
*/

//create config
if (!is_file('../config.cfg.php')) {
	$config = '<?php
/*
 * Verkeersbordenkaart configuration file
*/

//Database
$db[\'host\'] = \'localhost\';
$db[\'user\'] = \'root\';
$db[\'pass\'] = \'\';
$db[\'db\'] = \'verkeersborden\';

//Resources
$cfg_resource[\'API\'] = \'https://data.ndw.nu/api/rest/static-road-data/traffic-signs/v1/events\';
$cfg_resource[\'CURLOPT_SSL_VERIFYPEER\'] = TRUE;

//Tijdlimieten (seconden)
$cfg_runtime_limit = 160; //maximale tijd die het script actief mag zijn
$cfg_timeout_limit = 3000; //tijd die verstreken moet zijn om het script te mogen herstarten wanneer dit niet correct afgesloten is
$cfg_runonce_limit = 604800; //tijd die verstreken moet zijn om volledige scriptloop opnieuw te mogen beginnen (standaard 1x volledige uitvoer per week)

?>
';
	file_put_contents('../config.cfg.php', $config);
	echo 'created config file'.PHP_EOL;
	echo 'PLEASE EDIT CONFIG FILE AND RUN INSTALL AGAIN!'.PHP_EOL;
	exit;
}
else {
	include('../config.cfg.php');
	
	$db['link'] = mysqli_connect($db['host'], $db['user'], $db['pass']);
	mysqli_set_charset($db['link'], "utf8");
	
	$qry = "CREATE DATABASE IF NOT EXISTS `".$db['db']."`
	COLLATE 'utf8_general_ci'";
	if (mysqli_query($db['link'], $qry)) echo 'database created or exists'.PHP_EOL;
	else echo 'did not create database'.PHP_EOL;
	
	mysqli_select_db($db['link'], $db['db']);
	
	$qry = "CREATE TABLE IF NOT EXISTS `verkeersborden`
	(
		`id` BIGINT UNSIGNED NOT NULL PRIMARY KEY,
		`type` VARCHAR(16),
		`schema_version` VARCHAR(16),
		`publication_timestamp` DATETIME,
		`validated` BOOLEAN,
		`validated_on` DATE NULL,
		`user_id` INT UNSIGNED NOT NULL,
		`organisation_id` INT UNSIGNED NOT NULL,
		`rvv_code` VARCHAR(16),
		`text_signs` TINYTEXT,
		`location.wgs84.latitude` FLOAT,
		`location.wgs84.longitude` FLOAT,
		`location.rd.x` FLOAT,
		`location.rd.y` FLOAT,
		`location.placement` VARCHAR(1),
		`location.side` VARCHAR(1),
		`location.road.name` TINYTEXT,
		`location.road.type` INT(1) UNSIGNED NULL,
		`location.road.number` INT UNSIGNED,
		`location.road.wvk_id` INT UNSIGNED,
		`location.county.name` TINYTEXT,
		`location.county.code` VARCHAR(16),
		`location.county.townname` VARCHAR(128),
		`details.image` TINYTEXT,
		`details.first_seen` DATE,
		`details.last_seen` DATE,
		`details.removed` DATE NULL
	)
	ENGINE `MyISAM`,
	CHARACTER SET 'utf8', 
	COLLATE 'utf8_general_ci'";
	if (mysqli_query($db['link'], $qry)) echo 'table `verkeersborden` created or exists'.PHP_EOL;
	else echo 'did not create table `verkeersborden`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;

	include('addindex.php');

	$qry = "CREATE TABLE IF NOT EXISTS `updatelog`
	(
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`lastupdate` INT UNSIGNED NOT NULL,
		`starttime` INT UNSIGNED NOT NULL,
		`offset` VARCHAR(24),
		`error` BOOLEAN DEFAULT 0,
		`error_text` TINYTEXT,
		`finished` BOOLEAN NOT NULL DEFAULT 0
	)
	ENGINE `MyISAM`,
	CHARACTER SET 'utf8', 
	COLLATE 'utf8_general_ci'";
	if (mysqli_query($db['link'], $qry)) echo 'table `updatelog` created or exists'.PHP_EOL;
	else echo 'did not create table `updatelog`'.PHP_EOL;
	echo mysqli_error($db['link']).PHP_EOL;
	

}
?>