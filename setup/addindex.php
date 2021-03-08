<?php

// add spatial index (along with matching 'point' column) to database created in install.php


include('../dbconnect.inc.php'); // so script can be run independently from install.php
// Add spatial column
$qry = "alter table `verkeersborden` add `wgs84` point not null;";
$res = mysqli_query($db['link'], $qry);
// populate
$qry = "update `verkeersborden` set `wgs84` = Point (`location.wgs84.latitude`, `location.wgs84.longitude`);";
$res = mysqli_query($db['link'], $qry);
//create spatial index
$qry = "alter table `verkeersborden` add spatial index(`wgs84`);";
$res = mysqli_query($db['link'], $qry);


?>