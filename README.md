# lbs2daia - DAIA-API für LBS-Systeme

Das LBS 4 bietet seit Version 2.8.2 über eine Verfügbarkeits-API, mit der der
aktuelle Ausleihstatus von Titeln bzw. Exemplaren ermittelt werden kann. Konkret
gibt es zwei Schnittstellen:

* /webservices/availability/titles - Allgemeiner Status in Kurzform
* /webservices/availability/titleinfo - Genauer Exemplarstatus (Langform)

Der Quellcode in diesem Code-Repository setzt einen Wrapper von der API des LBS
auf das Format der Document Availability API (DAIA) um.

Ggf. wäre auch in Absprache mit dem LBS-Hersteller OCLC eine Integration in das
LBS-System möglich, so dass dieses direkt DAIA liefert - in diesem Fall wäre es
wahrscheinlich praktischer, lbs2daia nach Java zu portieren.

## Inhalt

* lbs2daia.php - Wrapper
* lib/         - Wrapper als PHP-Klasse und andere Libraries
* examples/    - Beispielanfragen und -Datensätze
* tests/       - Unit Tests für lbs2daia.php 
* README.md    - diese Datei

## Abhängigkeiten

* Getestet mit PHP 5.3.2
* Die Unit Tests im Verzeichnis tests/ benötigen PHPUnit
* Eine Kopie von phpDaia befindet sich in lib/phpDaia, geklont mittels
  `svn co https://daia.svn.sourceforge.net/svnroot/daia/trunk/phpDaia phpDaia`

