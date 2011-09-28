<?php

class LBS_Availability {
    var $lbsbase;
    var $dbkey;        # gbv:dbkey
    var $picabase;     # gbv:picabase
    var $homepage;     # foaf:homepage
    var $librarytitle;

    var $unapibase = 'http://unapi.gbv.de/';

    public function __construct( $dbkey, $config ) {
        $this->dbkey    = $dbkey;
        $this->picabase = $config['picabase']; # lässt sich auch perl Linked Data aus dbkey ermitteln
        $this->lbsbase  = $config['lbsbase'];
        $this->homepage = @$config['homepage'];
        if ($this->homepage) $this->homepage = $this->picabase;
        $this->librarytitle = @$config['librarytitle'];
    }

    /**
     * @param ppn       PPN record identifier
     * @param language  "en" (default) or "de"
     */
    public function getTitleinfoURL( $ppn, $language = 'en' ) {
        return $this->lbsbase . 'LBS_WEB/webservices/availability/titleinfo?'
            . http_build_query(array(
                'BES' => 1,
                'LAN' => ($language == 'de' ? 'DU' : 'EN'),
                'USR' => 1000,
                'PPN' => $ppn, 
            ));
    }

    // returns a DOMDocument or null
    public function getTitleinfoDOM( $ppn, $language = 'en' ) {
        $url = $this->getTitleinfoURL( $ppn, 'de' );
        try {
            $xml = @file_get_contents( $url );
            if (!$xml) throw new Exception("Failed to access $url");
            $xml = simplexml_load_string($xml);
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            return $dom;
        } catch ( Exception $e ) { }
        return null;
    }

    public function getRecordURI( $ppn ) {
        if (!$this->dbkey || !self::validPPN($ppn)) return;   

        return 'http://uri.gbv.de/record/' . $this->dbkey . ":ppn:$ppn";
    }

    public function getRecordLink( $ppn ) {
        if (!$this->picabase || !self::validPPN($ppn)) return;   

        return $this->picabase . "PPNSET?PPN=$ppn";
    }

    public function getPicaXML( $ppn ) {
        if (!$this->picabase || !self::validPPN($ppn)) return;   
        try {
            $url = $this->unapibase . '?format=picaxml&id=' . $this->dbkey . ":ppn:$ppn";
            $doc = new DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $doc->load( $url );
            return $doc;
        } catch ( Exception $e ) { }
        return null; 
    }

    public static function validPPN( $ppn ) {
        return preg_match('/^\d+[0-9X]?$/i', $ppn);
    }
}

?>
