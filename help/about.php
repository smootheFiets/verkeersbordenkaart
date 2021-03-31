<?php 
/*
 	Verkeersbordenkaart - Data uit NDW Verkeersborden API weergegeven op een kaart
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

include('../dbconnect.inc.php');

$qry = "SELECT * FROM `updatelog` ORDER BY `id` DESC LIMIT 1";
$res = mysqli_query($db['link'], $qry);
$data = mysqli_fetch_assoc($res);

echo '<p>Verkeersbordenkaart is laatst bijgewerkt op ' . date('Y-m-d H:i:s', $data['lastupdate']) . '. De meest actuele gegevens zijn van ' . date('Y-m-d H:i:s', strtotime($data['offset'])) . '.';

$qry = "SELECT count(*) FROM `verkeersborden`";
$res = mysqli_query($db['link'], $qry);
$row = mysqli_fetch_row($res);

echo ' De database van verkeersbordenkaart bevat momenteel ' . $row[0] . ' records.</p>';

?>
