<?php 
/*
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
//session_start();
//include_once('getuserdata.inc.php');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Verkeersbordenkaart - Over</title>
<link rel="stylesheet" type="text/css" href="bundled/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="style/style.css">
<link rel="icon" type="image/png" href="favicon.png">
<script type="text/javascript" src="bundled/jquery/jquery.min.js"></script>
<script type="text/javascript" src="bundled/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="help.js"></script>
</head>
<body>

<?php
include('menu.inc.php');
?>

<div id="content">
    <h1>Over Verkeersbordenkaart</h1>
    <p>Verkeersbordenkaart is een grafische interface voor het bekijken van de open data "Verkeersborden" van <a href="https://docs.ndw.nu/api/">https://docs.ndw.nu/api/</a>. Deze dataset bevat informatie en locaties van zo'n drie miljoen RVV-borden uit heel Nederland.</p>
    <p>Verkeersbordenkaart kopieert de data uit de dataset naar een lokale database en presenteert de inhoud van deze database op een kaartondergrond. De data die gepresenteerd wordt in Verkeersbordenkaart wordt iedere dag geactualiseerd.</p>
    <p>Verkeersbordenkaart is gemaakt door Jasper Vries. Er wordt geen aansprakelijkheid aanvaard voor compleetheid, juistheid, geschiktheid en correcte werking van deze website.</p>
    
    <h2>Toekomstige functionaliteiten</h2>
    <p>Melding tonen wanneer niet ver genoeg is ingezoomd.</p>

    <h2>Broncode</h2>
    <p>De broncode van Verkeersbordenkaart is gepubliceerd op <a href="https://github.com/jaspervries/verkeersbordenkaart/">GitHub</a>.</p>
    <p>Verkeersbordenkaart is gebouwd op de basis van <a href="http://assets.vcdh.nl/">Assetwebsite</a>.
</div>
</body>
</html>