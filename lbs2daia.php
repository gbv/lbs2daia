<?php

/**
 * Konfiguration für jeweiligen Katalog und Bibliothek
 */
$language = "de";
$config = array(
    "lbsbase"  => "http://opac.lbs-hildesheim.gbv.de:9090/",
    "dbkey"    => "opac-de-hil2",

    # Die folgenden Einstellungen können per LinkedData ermittelt werden
    # siehe http://uri.gbv.de/database/opac-de-hil2?format=svg
    "picabase" => "http://opac.lbs-hildesheim.gbv.de/DB=1/",
    "homepage" => "http://opac.lbs-hildesheim.gbv.de/DB=1/",
    "title"
);


include 'lib/LBS_Availability.php'; 
include 'lib/phpDaia/daia.php'; // TODO: Ausgabe direkt als DAIA/XML wäre einfacher

$lbs = new LBS_Availability( $config );


$rawavail = null;
$ppn = @$_GET['ppn'];

#$ppn = "667363327"; # zum Testen
if (!LBS_Availability::validPPN($ppn)) $ppn = "";

if ($ppn) {
    $rawavail = $lbs->getTitleinfoDOM($ppn, $language);
}

// Titel gefunden
$docs = array();
if ($rawavail) {

    $root = $rawavail->documentElement;

    // <labels> entfernen, da unnötig
    $labels = $root->firstChild;
    if ($labels and $labels->tagName == "labels") $root->removeChild( $labels );

    // start converting 
    // XML processing in PHP is cruel - maybe XSLT is a better choice?
    # $xpath = new DOMXpath($rawavail);

    $availability = $root->getElementsByTagName('availability')->item(0);
    if ($availability) $availability = $availability->textContent;

    if ($availability and $availability >= 0) {
        $id = $lbs->getRecordURI( $ppn );
        $href = $lbs->getRecordLink( $ppn );

        if ( $id ) {
            $doc = new DAIA_Document( $id, $href );

            # TODO: Exemplardaten hinzufügen

            $docs[] = $doc;
        }
    }
}

$daia = new DAIA( $docs );
$institution = new DAIA_Element( $lbs->librarytitle, 
        "http://uri.gbv.de/database/" . $lbs->dbkey, 
        $lbs->homepage );
$daia->setInstitution( $institution );

if (!$docs) {
    $daia->setMessage(new DAIA_Message("PPN not found!", 'en', 100));
}


// Ausgabe erstmal nur als DAIA/XML

# this implementation only supports XML format

header ("Content-Type:text/xml; charset=UTF-8");

$daiaxml = new DAIA_XML( $daia );
$dom     = $daiaxml->createNamespacedXml();

if ($rawavail) {
    $copy = $dom->importNode( $rawavail->documentElement, true );
    $dom->documentElement->appendChild( new DOMComment( "die interne LBS-API-Antwort, die auf DAIA gemappt werden muss" ) );
    $dom->documentElement->appendChild( $copy );
}

$pica = $lbs->getPicaXML( $ppn );
if ($pica) {
    $dom->documentElement->appendChild( new DOMComment( "Relevante Felder aus dem PICA/XML Lokaldatensatz" ) );
    foreach ($pica->documentElement->childNodes as $child) {
        if ($child->nodeType != 1 || $child->tagName != 'datafield') continue;
        $tag = $child->getAttribute('tag');#->value;
        // Feld 101@ und 201@ werden ggf. benötigt für Standort-URIs
        if ($tag != '101@' && $tag != '201@') continue; 
        $field = $dom->importNode( $child, true );
        $dom->documentElement->appendChild( $field );
    }
}

print $dom->saveXml();

?>
