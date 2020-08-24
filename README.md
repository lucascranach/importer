# Importer

Knoten-basierter Importer für Cranach-XML Dateien mit flexiblen Ausgabemöglichkeiten (momentan erstmal JSONs).


## Vorbereitung 

Da dieses Projekt auf Composer für das Package-Management setzt, muss dies im ersten Schritt global installiert werden.
Dazu sei auf die [Installationsanweisungen](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) von Composer verwiesen.
Unter MacOS kann für eine schnelle Installation auch `brew install composer` genutzt werden, sofern `brew` installiert ist.

## Scripts
Um die Nutzung des Importers (aber auch die Entwicklung) zu vereinfachen, kommt das Projekt mit einige Composer-Scripts:

| Name | Beschreibung |
| :-- | :-- |
| lint | Auflistung von Dateien mit Linter-Problemen |
| lint:fix | Auflistung von Dateien mit Linter-Problemen und automatische Behebung, sofern möglich |
| analyse | Analyse der  im `src`-Ordner zu findenden PHP-Dateien |
| **import** | Starten des Import-Vorgangs; alternativ  `php index.php` ausführen |


## Getting started

### Importierung
1. Um neue XML-Dateien importieren zu können, sollten diese im `input`-Ordner unter einem eigenen Ordner nach dem Muster `yyyymmdd` abgelegt werden
2. In der `index.php` den Import- und Output-Pfad aktualisieren, damit auch die neuen Dateien für den Import berücksichtigt werden
3. Ggf. diverse Bereiche mit Loader-Initialisierungen in der `index.php` auskommentieren, wenn die dafür notwendigen XML-Dateien nicht vorliegen
4. `composer run-script import` ausführen

### Entwicklung
Generell sollte immer der über Composer eingebundene Linter und Code-Analyser genutzt werden, um frühzeitig mögliche Formatierungs- und Code-Probleme erkennen und beheben zu können.

Als Linter wird [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) mit den in der `.php_cs.dist`angegebenen Rules genutzt.  
Als Analyser kommt hingegen [PHPStan](https://github.com/phpstan/phpstan) zum Einsatz (in [Rule Level 5](https://phpstan.org/user-guide/rule-levels)).