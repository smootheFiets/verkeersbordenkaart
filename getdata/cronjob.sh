#!/bin/bash
cd /var/www/html/verkeersbordenkaart/getdata
touch cronresult.txt
php -f ./cronjob.php >> cronresult.txt 2>&1
