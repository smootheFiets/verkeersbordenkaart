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

set_time_limit(0);
include('../config.cfg.php');

$time_start = time();

//connect met database
$db['link'] = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['db']);
mysqli_set_charset($db['link'], "latin1");

//verkrijg meest recente update status
$qry = "SELECT * FROM `updatelog` ORDER BY `id` DESC LIMIT 1";
$res = mysqli_query($db['link'], $qry);
if (mysqli_num_rows($res)) {
	$updatestate = mysqli_fetch_assoc($res);
}
else {
	//dit is de allereerste entry
	$updatestate = array('finished' => 1);
}

//controleer of script gestart mag worden
if ($updatestate['finished'] == 0) {
	//script is nog niet klaar
	//als nog geen timeout, exit
	if (($updatestate['lastupdate'] + $cfg_timeout_limit) > $time_start) {
		echo 'no timeout yet' . PHP_EOL;
		echo $updatestate['lastupdate'] . PHP_EOL;
		echo $time_start . PHP_EOL;
		exit;
	}
}
else {
	//script is klaar
	//als te vroeg om opnieuw te beginnen, exit
	if (($updatestate['starttime'] + $cfg_runonce_limit) > $time_start) {
		echo 'no allowed to start yet' . PHP_EOL;
		echo $updatestate['starttime'] . PHP_EOL;
		echo $time_start . PHP_EOL;
		exit;
	}
}

function curl_get_contents($url, $verifyPeer) {
	$error = 0;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifyPeer);
	curl_setopt($ch, CURLOPT_URL, $url);
	$contents = curl_exec($ch);
	if ($contents === FALSE) {
		$error = 1;
		$contents = curl_error($ch);
	}
	else {
		$http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		if ($http != 200) {
			$error = 1;
			$contents = 'HTTP ' . $http . ': ' . $contents;
		}
	}
	curl_close($ch);
	return array($error, $contents);
}

function date_to_db($date) {
	if (preg_match('#\d{2}/\d{2}/\d{4}#', $date)) {
		return "'" . substr($date, 6, 4) . '-' . substr($date, 3, 2) . '-' . substr($date, 0, 2) . "'";
	}
	else {
		return 'NULL';
	}
}


//voer taak uit
while(TRUE) {

	//haal data van API
	$url = $cfg_resource['API'];
	$verifyPeer = $cfg_resource['CURLOPT_SSL_VERIFYPEER'];
	if (!empty($updatestate)) {
		$url .= '?offset=' . $updatestate['offset'];
	}
	$data = curl_get_contents($url, $verifyPeer);

	//verwerk api error
	if ($data[0] == 1) {
		//error, zet in log
		$qry = "INSERT INTO `updatelog` SET
		`lastupdate` = " . time() . ",
		`starttime` = " . $time_start . ",
		`error` = 1,
		`error_text` = '" . mysqli_real_escape_string($db['link'], $data[1]) . "'";
		if (!empty($updatestate['offset'])) {
			$qry .= ", `offset` = '" . $updatestate['offset'] . "'";
		}
		mysqli_query($db['link'], $qry);
		exit;
	}
	else {
		//geen error, verwerk data
		$json = json_decode($data[1]);

		//if json is empty, then there is nothing to do
		if (empty($json)) {
			$qry = "UPDATE `updatelog` SET
			`finished` = 1
			ORDER BY `id` DESC
			LIMIT 1";
			mysqli_query($db['link'], $qry);
			exit;
		}

		foreach ($json as $item) {
			//var_dump($item); exit;
			//TODO check data contents

			// Data to be sanitized (my PHP appears to be fussy)
			if ($item->validated == 'n') 
			   $item->validated=0;
			else 
			   $item->validated=1;
			if ($item->user_id=='') $item->user_id=0;
			if ($item->organisation_id=='') $item->organisation_id=0;
			if ($item->location->road->wvk_id=='') $item->location->road->wvk_id=0;
			if ($item->location->road->type=='') $item->location->road->type=0;			
			if ($item->location->road->number=='') $item->location->road->number=0;
			// And here's to (my) MySQL not accepting ISO8601 dates:
			$item->publication_timestamp_output=date_format(date_create($item->publication_timestamp), "Y-m-d H:i:s.v");
			// text_signs is MYSQL TINYTEXT, max 255 characters. Crop any excess characters to avoid error:
			$text255 = substr(json_encode($item->text_signs),1,-1); # strip starting and trailing "
			$text255 = '"' . substr($text255,0,253) . '"';
			// location.side is sometimes 'bord.schouw onbekend', setting to 'X' (one-char limit)
			if (strlen( $item->location->side ) > 1) $item->location->side='X';
			if ($item->location->road->number=='') $item->location->road->number=0;			    // And here's to (my) MySQL not accepting ISO8601 dates:
			$item->publication_timestamp_output=date_format(date_create($item->publication_timestamp), "Y-m-d H:i:s.v");

			$contents = "`type` = '" . mysqli_real_escape_string($db['link'], $item->type) . "',
			`schema_version` = '" . mysqli_real_escape_string($db['link'], $item->schema_version) . "',
			`publication_timestamp` = '" . mysqli_real_escape_string($db['link'], $item->publication_timestamp_output) . "',
			`validated` = '" . mysqli_real_escape_string($db['link'], $item->validated) . "',
			`validated_on` = " . date_to_db($item->validated_on) . ",
			`user_id` = '" . mysqli_real_escape_string($db['link'], $item->user_id) . "',
			`organisation_id` = '" . mysqli_real_escape_string($db['link'], $item->organisation_id) . "',
			`rvv_code` = '" . mysqli_real_escape_string($db['link'], $item->rvv_code) . "',
			`text_signs` = '" . mysqli_real_escape_string($db['link'], $text255) . "',
			`location.wgs84.latitude` = '" . mysqli_real_escape_string($db['link'], $item->location->wgs84->latitude) . "',
			`location.wgs84.longitude` = '" . mysqli_real_escape_string($db['link'], $item->location->wgs84->longitude) . "',
			`wgs84` = geomfromtext('Point(" . mysqli_real_escape_string($db['link'], $item->location->wgs84->latitude) .
			                            " " . mysqli_real_escape_string($db['link'], $item->location->wgs84->longitude) . ")'),
			`location.rd.x` = '" . mysqli_real_escape_string($db['link'], $item->location->rd->x) . "',
			`location.rd.y` = '" . mysqli_real_escape_string($db['link'], $item->location->rd->y) . "',
			`location.placement` = '" . mysqli_real_escape_string($db['link'], $item->location->placement) . "',
			`location.side` = '" . mysqli_real_escape_string($db['link'], $item->location->side) . "',
			`location.road.name` = '" . mysqli_real_escape_string($db['link'], $item->location->road->name) . "',
			`location.road.type` = '" . mysqli_real_escape_string($db['link'], $item->location->road->type) . "',
			`location.road.number` = '" . mysqli_real_escape_string($db['link'], $item->location->road->number) . "',
			`location.road.wvk_id` = '" . mysqli_real_escape_string($db['link'], $item->location->road->wvk_id) . "',
			`location.county.name` = '" . mysqli_real_escape_string($db['link'], $item->location->county->name) . "',
			`location.county.code` = '" . mysqli_real_escape_string($db['link'], $item->location->county->code) . "',
			`location.county.townname` = '" . mysqli_real_escape_string($db['link'], $item->location->county->townname) . "',
			`details.image` = '" . mysqli_real_escape_string($db['link'], $item->details->image) . "',
			`details.first_seen` = " . date_to_db($item->details->first_seen) . ",
			`details.last_seen` = " . date_to_db($item->details->last_seen) . ",
			`details.removed` = " . date_to_db($item->details->removed) . "";

			$qry = "INSERT INTO `verkeersborden` SET
				`id` = '" . mysqli_real_escape_string($db['link'], $item->id) . "'," 
				. $contents . 
				" ON DUPLICATE KEY UPDATE "
				. $contents;
			if (! mysqli_query($db['link'], $qry) ) {
			   echo mysqli_error($db['link']), "\n";
			   print "$qry\n\n";
			}

			//laatste publication_timestamp
			if (preg_match('#\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z#', $item->publication_timestamp)) {
				//alleen bijwerken als timestamp voldoet aan syntaxis
				$updatestate['offset'] = $item->publication_timestamp;
				$lastid = $item->id;
			}
			
		}
		//check if we have a duplicate last id
		if (isset($previouslastid) && ($previouslastid == $lastid)) {
			//we've reached the last item, nothing more to do
			if ($updatestate['finished'] == 0) {
				$qry = "UPDATE `updatelog` SET
				`lastupdate` = " . time() . ",
				`finished` = 1
				ORDER BY `id` DESC
				LIMIT 1";
			}
			else {
				$qry = "INSERT INTO `updatelog` SET
				`lastupdate` = " . time() . ",
				`starttime` = " . $time_start . ",
				`offset` = '" . $updatestate['offset'] . "',
				`finished` = 1";
			}
			mysqli_query($db['link'], $qry);
			exit;
		}
		else {
			$previouslastid = $lastid;
		}
	}

	//exit;
	//bepaal resterende tijd
	$time_left = $cfg_runtime_limit - 5 - (time() - $time_start); //vijf seconden marge
	//als er minder dan tien seconden zijn om iets te doen
	if ($time_left < 10) {
		//er is niet genoeg tijd om iets nieuws te beginnen
		//werk updatelog bij
		$qry = "INSERT INTO `updatelog` SET
		`lastupdate` = " . time() . ",
		`starttime` = " . $time_start . ",
		`offset` = '" . $updatestate['offset'] . "'";
		mysqli_query($db['link'], $qry);
		exit;
	}
}

?>
