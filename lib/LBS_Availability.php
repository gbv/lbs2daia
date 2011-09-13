<?php

class LBS_Availability {
    var $lbsbase;
    var $dbkey;
    var $unapibase = 'http://unapi.gbv.de/';

    public function __construct( $config ) {
        $this->lbsbase = $config['lbsbase'];
        $this->dbkey   = @$config['dbkey2'];
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
            $xml = file_get_contents( $url );
            $xml = simplexml_load_string($xml);
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            return $dom;
        } catch ( Exception $e ) {
        }
        return null;
    }
}

?>
