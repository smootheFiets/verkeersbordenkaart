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

$image = 'images/' . preg_replace('/[^[:alnum:\()]]/i', '', $_GET['i']) . '.png';


if (file_exists($image)) {
    echo file_get_contents($image);
}
else {
    echo file_get_contents('style/genericicon.png');
}
exit;

?>