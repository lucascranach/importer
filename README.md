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
| clear:cache | Lösche den Cache-Ordner |
| **import** | Starten des Import-Vorgangs; alternativ  `php index.php` ausführen |
| import:skip-deleted | Starten des Import-Vorgangs und überspringt als gelöscht markierten Artefakte; alternativ  `php index.php -x` ausführen |


## Getting started

### Umgebungsvariablen
Für den Importvorgang müssen bestimmte Umgebungsvariablen gesetzt sein. Darunter u.a. ein Access-Key für die entfernt liegenden Bildinformationen.
Der Importer geht davon aus, dass im Wurzelverzeichnis eine `.env`-Datei existiert, die als Basis für die Durchreichung von u.a. sensiblen Daten dient.
Die `.env` sollte am besten von der existierenden `.env.example` abgeleitet und anschließend angepasst werden:

```bash
cp .env.example .env
```

### Importierung
1. Um neue XML-Dateien importieren zu können, sollten diese im `input`-Ordner unter einem eigenen Ordner nach dem Muster `yyyymmdd` abgelegt werden
2. In der `index.php` den Import- und Output-Pfad aktualisieren, damit auch die neuen Dateien für den Import berücksichtigt werden
3. Ggf. diverse Bereiche mit Loader-Initialisierungen in der `index.php` auskommentieren, wenn die dafür notwendigen XML-Dateien nicht vorliegen
4. `composer run-script import` ausführen

### In-/ und Output Verzeichnisse
Die Input und Output Verzeichnisse sind jetzt nicht mehr Teil des Repos. Daten bitte hierher beziehen, bzw. ablegen:

- Input `~/sciebo/cranach/exporte`
- Output `~/sciebo/cranach/json-output`

Bereitgestellte Daten werden bei Bedarf über den Zweig `legacy-exchange` bereit gestellt. 


### Entwicklung
Generell sollte immer der über Composer eingebundene Linter und Code-Analyser genutzt werden, um frühzeitig mögliche Formatierungs- und Code-Probleme erkennen und beheben zu können.

Als Linter wird [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) mit den in der `.php_cs.dist`angegebenen Rules genutzt.  
Als Analyser kommt hingegen [Psalm](https://psalm.dev/) zum Einsatz (in [ErrorLevel 3](https://psalm.dev/docs/running_psalm/error_levels/).