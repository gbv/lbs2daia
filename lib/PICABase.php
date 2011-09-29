<?php

/**
 * Kapselt verschiedene HTTP-Zugriffe auf eine PICA-Datenbank.
 */
class PICABase {
    protected $dbkey;    # gbv:dbkey
    protected $picabase; # gbv:picabase

    public function __construct( $dbkey, $picabase ) {
        $this->dbkey    = $dbkey;
        $this->picabase = $picabase;

        if (!self::validDBkey( $dbkey )) 
            throw new Exception("Invalid gbv:dbkey: $dbkey");

        if (!self::validURL( $picabase ) )
            throw new Exception("Invalid gbv:picabase: $picabase");
    }

    // URL in den Katalog
    public function getRecordLink( $ppn ) {
        if (!self::validPPN($ppn)) return;   
        return $this->picabase . "PPNSET?PPN=$ppn";
    }

    // PICA+ Datensatz via unAPI in PICA/XML
    public function getPicaXML( $ppn ) {
        if (!self::validPPN($ppn)) return;   
        try {
            $url = 'http://unapi.gbv.de/?format=picaxml&id=' . $this->dbkey . ":ppn:$ppn";
            $doc = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $doc->load( $url );
            return $doc->documentElement;
        } catch ( Exception $e ) { 
            return null; 
        }
    }


    public static function validDBKey( $dbkey ) {
        return preg_match('|[a-z]+(-[a-z0-9]+)?|', $dbkey);
    } 
    public static function validPPN( $ppn ) {
        return preg_match('/^\d+[0-9X]?$/i', $ppn);
    }
    public static function validURL( $url ) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }
}

?>
