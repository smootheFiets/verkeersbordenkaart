<?php
/*
 	fietsviewer - grafische weergave van fietsdata
	Copyright (C) 2018 Gemeente Den Haag, Netherlands
	assetwebsite - viewer en aanvraagformulier voor verkeersmanagementassets
    Copyright (C) 2020 Gemeente Den Haag, Netherlands
	Developed by Jasper Vries
	Modified for Verkeersbordenkaart
    Copyright (C) 2020 Jasper Vries
 
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

session_start();
include('dbconnect.inc.php');
require('functions/bounds_to_sql.php');

//popup contents
if ($_GET['get'] == 'popup') {
	$json = array('html' => '');
	//query om inhoud van tabel te selecteren
	$qry = "SELECT `id`, `type`, `rvv_code`, `text_signs`, `location.placement`, `location.side`, `location.road.name`, `location.road.type`, `location.road.number`, `location.county.name`, `details.first_seen`, `details.last_seen`, `details.removed`, `location.wgs84.latitude`, `location.wgs84.longitude`
	FROM `verkeersborden`
	WHERE `id` = '" . mysqli_real_escape_string($db['link'], $_GET['id']) . "'";
	$qry .= " LIMIT 1";
	//voer query uit
	$res = mysqli_query($db['link'], $qry);
	//als er een of meer rijen zijn
	if (mysqli_num_rows($res) > 0) {
		$data = mysqli_fetch_assoc($res);
		$html = '<img src="image.php?i=' . $data['rvv_code'] . '" style="max-width: 64px; max-height: 64px;">';
		$html .= '<table>';
		foreach ($data as $k => $v) {
			$html .= '<tr><th>';
			$html .= htmlspecialchars($k);
			$html .= '</th><td>';
			$html .= htmlspecialchars($v);
			$html .= '</td></tr>';
		}
		$html .= '</table>';
		$data['heading'] = '0';
		$html .= '<p><a id="popup_details">Details bekijken</a></p> ';
		$html .= '<p>Open locatie in <a href="https://www.openstreetmap.org/#map=18/' . $data['location.wgs84.latitude'] .'/' . $data['location.wgs84.longitude'] . '" target="_blank">OpenStreetMap</a>';
		$html .= ', (<a href="https://osmose.openstreetmap.fr/en/josm_proxy?zoom?left=' . $data['location.wgs84.longitude'] . '&bottom=' . $data['location.wgs84.latitude'] . '&right=' .  $data['location.wgs84.longitude'] . '&top=' . $data['location.wgs84.latitude'] . '" target="hiddenIframe">JOSM remote-control</a>)';
		$html .= ', <a href="https://www.mapillary.com/app/?z=18&lat=' . $data['location.wgs84.latitude'] . '&lng=' . $data['location.wgs84.longitude'] . '" target="_blank">Mapillary</a>; ';
		$html .= '<a href="https://' . $_SERVER['HTTP_HOST'] . '/html/verkeersbordenkaart/index.php?id=' .  $_GET['id'] . '" target="_blank">permalink naar dit bordje</a></p>';


		$html .= '<p>Open locatie in <a href="https://www.google.nl/maps/?q=' . $data['location.wgs84.latitude'] . ',' . $data['location.wgs84.longitude'] . '&amp;layer=c&cbll=' . $data['location.wgs84.latitude'] . ',' . $data['location.wgs84.longitude'] . '&amp;cbp=11,' . $data['heading'] . ',0,0,5" target="_blank">Google Street View&trade;</a></p>';
		$json['html'] = $html;
	}
	else {
		$json['html'] = '<p class="error">Geen detailinformatie gevonden' . $qry .'</p>';
	}
}

//details window
elseif ($_GET['data'] == 'details') {
	$json = array('html' => '', 'title' => '');
	//query om inhoud van tabel te selecteren
	$qry = "SELECT *
	FROM `verkeersborden`
	WHERE `id` = '" . mysqli_real_escape_string($db['link'], $_GET['id']) . "'";
	$qry .= " LIMIT 1";
	//voer query uit
	$res = mysqli_query($db['link'], $qry);
	//als er een of meer rijen zijn
	if (mysqli_num_rows($res) > 0) {
		$html = '<div style="float:left; margin-left: 8px; max-width: calc(100% - 600px);">';
		$data = mysqli_fetch_assoc($res);
		$html .= '<img src="image.php?i=' . $data['rvv_code'] . '" style="max-width: 64px; max-height: 64px;">';
		//tabel
		$html .= '<table>';
		foreach ($data as $k => $v) {
			$html .= '<tr><th>';
			$html .= htmlspecialchars($k);
			$html .= '</th><td>';
			$html .= htmlspecialchars($v);
			$html .= '</td></tr>';
		}
		$html .= '</table>';

		$html .= '<input type="hidden" id="latitude" value="' . htmlspecialchars($data['location.wgs84.latitude']) . '">';
		$html .= '<input type="hidden" id="longitude" value="' . htmlspecialchars($data['location.wgs84.longitude']) . '">';
		$html .= '<input type="hidden" id="heading" value="0">';

		$html .= '</div>';

		//minimap
		$html .= '<div style="float:left; margin-left: 8px;">';
		$html .= '	<div id="minimap" style="width: 400px; height: 400px;"></div>';

		// OSM link
		$html .= '<p>Open locatie in <a href="https://www.openstreetmap.org/#map=18/' . $data['location.wgs84.latitude'] .'/' . $data['location.wgs84.longitude'] . '" target="_blank">OpenStreetMap</a>';
		$html .= ', (<a href="https://osmose.openstreetmap.fr/en/josm_proxy?zoom?left=' . $data['location.wgs84.longitude'] . '&bottom=' . $data['location.wgs84.latitude'] . '&right=' .  $data['location.wgs84.longitude'] . '&top=' . $data['location.wgs84.latitude'] . '" target="hiddenIframe">JOSM remote-control</a>)';
		$html .= ', <a href="https://www.mapillary.com/app/?z=18&lat=' . $data['location.wgs84.latitude'] . '&lng=' . $data['location.wgs84.longitude'] . '" target="_blank">Mapillary</a></p>';
		//streetview link
		$data['heading'] = '0';
		$html .= '<p>Open locatie in <a href="https://www.google.nl/maps/?q=' . $data['location.wgs84.latitude'] . ',' . $data['location.wgs84.longitude'] . '&amp;layer=c&cbll=' . $data['location.wgs84.latitude'] . ',' . $data['location.wgs84.longitude'] . '&amp;cbp=11,' . $data['heading'] . ',0,0,5" target="_blank">Google Street View&trade;</a></p>';
		$html .= '<p><a href="https://' . $_SERVER['HTTP_HOST'] . '/html/verkeersbordenkaart/index.php?id=' .  $_GET['id'] . '" target="_blank">Permalink naar dit bordje</a></p>';
		

		//preview image
		$html .= '<p><img src="' . $data['details.image'] . '" style="max-width: 400px; max-heigth: 400px;></p>';

		$html .= '</div>';
		$html .= '<div style="clear:both;"></div>';

		$json['html'] = $html;
		$json['title'] = htmlspecialchars('Verkeersbord ' . $data['id'] . ' - ' . $data['rvv_code']);
	}
	else {
		$json['html'] = '<p class="error">Geen detailinformatie gevonden</p>';
		$json['title'] = 'Geen gegevens beschikbaar';
	}
}

//marker layer
else {
	$json = array();
	if ($_GET['zoom'] >= 15) {
		//query voor verkeersborden
		$qry = "SELECT `id` AS `assetid`, `rvv_code` AS `code`, `location.wgs84.latitude` AS `latitude`, `location.wgs84.longitude` AS `longitude` FROM `verkeersborden`
		WHERE " . bounds_to_sql($_GET['bounds']);
		$res = mysqli_query($db['link'], $qry);
		while ($data = mysqli_fetch_assoc($res)) {
			$json[] = array('id' => (int) $data['assetid'],
			'code' => $data['code'],
			'lat' => (float) $data['latitude'],
			'lon' => (float) $data['longitude'],
			//'heading' => 0,
			//'icon' => 1,
			//'itype' => 0,
			//'status' => 1
		);
		}
	}
}

// for centerMapAtID
if ($_GET['get'] == 'coordinates') {
  $qry = "SELECT `location.wgs84.latitude` AS `latitude`, `location.wgs84.longitude` AS `longitude` FROM `verkeersborden`
	WHERE `id` = '" . mysqli_real_escape_string($db['link'], $_GET['id']) . "'";
  $qry .= " LIMIT 1";
  //voer query uit
  $res = mysqli_query($db['link'], $qry);  
  while ($data = mysqli_fetch_assoc($res)) {
    $json = array (
		   latitude =>  (float) $data['latitude'],
		   longitude=>  (float) $data['longitude'],
		   );
  }
}


header('Content-Type: application/json');
echo json_encode($json);

?>