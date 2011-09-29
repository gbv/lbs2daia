<?php

/**
 * Liefert ein Interface zu einem GBV-OPAC, um darüber die Verfügbarkeit zu ermitteln.
 *
 * Da noch nicht vollständig feststeht, wie die für Verfügbarkeit notwendigen 
 * Angaben konfiguriert werden, ist diese Datei erstmal experimentell und beinhaltet
 * Nur eine überschaubare Zahl vorkonfigurierter Bibliotheken.
 */

include_once 'lib/PICABase.php';
include_once 'lib/LBS4Loan.php';


class AvailabilityInterface {
    public $dbkey;
    public $lbs4loan;
    public $picabase;
    public $homepage;
    public $librarytitle;

    public function __construct( $dbkey ) {
        $this->dbkey = $dbkey;
    }

    public function getDatabaseURI() {
        return 'http://uri.gbv.de/database/' . $this->dbkey;        
    }

    public function getRecordURI( $ppn ) {
        if (!PICABase::validPPN($ppn)) return;   
        return 'http://uri.gbv.de/record/' . $this->dbkey . ":ppn:$ppn";
    }
}


// Die Eigenschaften ergeben sich aus der Datenbank-Konfiguration uns sollten
// unter http://uri.gbv.de/database/$dbkey abrufbar sein. Momentan sind einige
// Datenbanken hier fest konfiguriert.
 
function getAvailabilityInterface( $dbkey ) {
    if (!preg_match('/^opac-/',$dbkey) or !PICABase::validDBKey( $dbkey )) return;

    // Konfiguration für die jeweiligen Katalog und Bibliotheken
    $properties = array(

        // Hildesheim
        # Die folgenden Einstellungen können per LinkedData ermittelt werden
        # siehe http://uri.gbv.de/database/opac-de-hil2?format=svg
        "opac-de-hil2" => array(
            "lbs4loanbase"  => "http://opac.lbs-hildesheim.gbv.de:9090/LBS_WEB/",
            "picabase" => "http://opac.lbs-hildesheim.gbv.de/DB=1/",
            "homepage" => "http://opac.lbs-hildesheim.gbv.de/DB=1/",
        ),
        // ZBW Kiel
        "opac-de-206" => array(
            "lbs4loanbase"  => "",
            "picabase" => "http://www.econis.eu/DB=1/",
            "homepage" => "http://www.econis.eu/DB=1/",
        ),
    );
    
    $config = @$properties[ $dbkey ];
    if (!$config) return;

    $opac = new AvailabilityInterface( $dbkey );
    if ( @$config["lbs4loanbase"] ) {
        $opac->lbs4loan = new LBS4Loan( $config["lbs4loanbase"] );
    }

    if ( @$config["picabase"] ) {
        $opac->picabase = new PICABase( $dbkey, $config["picabase"] );
    }

    $opac->homepage = @$config['homepage'];
    if (!$opac->homepage) $opac->homepage = @$config["picabase"];
    $opac->librarytitle = @$config['librarytitle'];

    return $opac;
}

?>
