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

$image = preg_replace('/[^[:alnum:\()]]/i', '', $_GET['i']);
if (in_array($_GET['s'], array(16, 24, 32))) {
    $image = 'images/icon' . $_GET['s'] . '/' . $image . '.png';
}
else {
    $image = 'images/' . $image . '.png';
}


if (file_exists($image)) {
    echo file_get_contents($image);
}
else {
    echo file_get_contents('style/genericicon.png');
}
exit;

?>