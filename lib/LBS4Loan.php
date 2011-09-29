<?php

/**
 * Kapselt die Verfügbarkeits-API eines LBS-Ausleihsystem (LBS4 ab Version 2.8.2).
 */
class LBS4Loan {
    // Die Basis-URL ist konfiguriert in /pica/opc4_beh/confdir/FILEMAP
    // In RDF wird sie mit der property gbv:lbs4loanbase abgebildet.
    protected $baseurl;

    public function __construct( $base ) {
        $this->baseurl = $base;
    }

    public function getTitleinfoURL( $ppn, $language = 'en' ) {
        return $this->baseurl . 'webservices/availability/titleinfo?'
            . http_build_query(array(
                'BES' => 1,
                'LAN' => ($language == 'de' ? 'DU' : 'EN'),
                'USR' => 1000,
                'PPN' => $ppn, 
            ));
    }

    public function getTitleinfoXML( $ppn, $language = 'en' ) {
        $url = $this->getTitleinfoURL( $ppn, 'de' );
        try {
            $xml = @file_get_contents( $url );
            if (!$xml) throw new Exception("Failed to access $url");
            $xml = simplexml_load_string($xml);
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            return $dom->documentElement;
        } catch ( Exception $e ) { 
            return null;
        }
    }
}

?>
