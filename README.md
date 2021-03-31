# Verkeersbordenkaart
Data uit NDW Verkeersborden API weergegeven op een kaart

Verkeersbordenkaart is een grafische interface voor het bekijken van de 
open data "Verkeersborden" van https://docs.ndw.nu/api/
Deze dataset bevat informatie en locaties van zo'n drie miljoen 
RVV-borden uit heel Nederland.

Verkeersbordenkaart kopieert de data uit de dataset naar een lokale database 
en presenteert de inhoud van deze database op een OSM ondergrond. 
Afbeeldingen van verkeersborden worden direct geladen vanaf de open data 
server, wanneer deze beschikbaar zijn.


# 0. Inhoudsopgave

1. Systeemvereisten en benodigdheden
1. Installatie
1. Cronjob
1. Licentie
1. Verkrijgen van de broncode


# 1. Systeemvereisten en benodigdheden

Voor de grafische interface is een recente webbrowser met 
ondersteuning voor HTML5 nodig. Primaire ontwikkeling vindt plaats 
voor Mozilla Firefox.

Voor de backend is een webserver met PHP (5.3+) en MySQL (5+) of 
MariaDB (5+) nodig. Optioneel kan Cron of een vergelijkbare toepassing 
worden ingezet om de database automatisch periodiek bij te werken.

URLs:
* Mozilla Firefox: https://www.mozilla.org/firefox
* Chromium: https://www.chromium.org
* Chromium (Windows build): http://chromium.woolyss.com
* PHP: http://php.net
* MySQL: https://www.mysql.com
* MariaDB: https://mariadb.org


# 2. Installatie

De installatie maakt de databasetabellen aan.
Voor toegankelijkheid op de HTML-server dient het mapje verkeersbordenkaart
naar /var/www/html/ gekopieërd te worden.
Voer setup/install.php uit vanuit een opdrachtregel. Als er nog 
geen configuratiebestand aanwezig is, wordt dit aangemaakt. Open dit 
met een teksteditor en vul de juiste databasecredentials is. Voer 
hierna install.php nogmaals uit om de databasetabellen aan te maken.

Wanneer de installatie gereed is kunnen de tabellen worden gevuld. Voer 
hiervoor getdata/cronjob.php uit. Er gelden timeouts zoals geconfigureerd in
config.cfg.php. Afhankelijk van de ingestelde waarden moet cronjob.php
meermaals worden uitgevoerd om de volledige dataset binnen te halen.


# 3. Cronjob

Het proces van het bijwerken van Verkeersbordenkaart kan geautomatiseerd 
worden door middel van een cronjob. cronjob.php is ontworpen om ieder 
uur te draaien en steeds een stukje bij te werken. Hierdoor kan het ook 
gebruikt worden op shared hosting omgevingen met beperkte rekentijd. Om 
de update in een keer uit te voeren kunnen de tijdlimieten in 
config.cfg.php worden aangepast.

Voorbeeld crontab:
```crontab
# verkeersbordenkaart update iedere uur op 47 minuten na het hele uur
47 * * * * php -f /var/www/html/verkeersbordenkaart/cronjob.php > /var/www/html/verkeersbordenkaart/cronresult.txt
```

# 4. Licentie

De broncode van Verkeersbordenkaart is vrijgegeven als open source software 
onder de GNU General Public License versie 3. 
Dit geeft iedereen het recht om de software te gebruiken en 
te kunnen beschikken over de broncode. Het maken van aanpassingen is 
eveneens toegestaan, zolang auteursrechtvermeldingen intact blijven, en 
vallen automatisch onder dezelfde licentievoorwaarden. Gebruikers van 
een aangepaste versie hebben daardoor ook het recht om te beschikken 
over de broncode van de aangepaste versie. Voor meer informatie zie de 
volledige licentietekst in COPYING.


Verkeersbordenkaart - Data uit NDW Verkeersborden API weergegeven op een kaart
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


# 5. Verkrijgen van de broncode

De broncode van Verkeersbordenkaart is gepubliceerd op GitHub.
https://github.com/jaspervries/verkeersbordenkaart
