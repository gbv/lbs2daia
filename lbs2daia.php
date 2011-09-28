<?php

/**
 * Konfiguration für die jeweiligen Katalog und Bibliotheken
 */
$language = "de";
$databases = array(

    // Hildesheim
    "opac-de-hil2" => array(
        "lbsbase"  => "http://opac.lbs-hildesheim.gbv.de:9090/",

        # Die folgenden Einstellungen können per LinkedData ermittelt werden
        # siehe http://uri.gbv.de/database/opac-de-hil2?format=svg
        "picabase" => "http://opac.lbs-hildesheim.gbv.de/DB=1/",
        "homepage" => "http://opac.lbs-hildesheim.gbv.de/DB=1/",
    ),
    // ZBW Kiel
    "opac-de-206" => array(
        "lbsbase"  => "",
        "picabase" => "http://www.econis.eu/DB=1/",
        "homepage" => "http://www.econis.eu/DB=1/",
    ),
);


include 'lib/LBS_Availability.php'; 
include 'lib/phpDaia/daia.php'; // TODO: Ausgabe direkt als DAIA/XML wäre einfacher

$lbs = NULL;
$ppn = NULL;
$pica = NULL;
$errors = array();

$rawavail = null;
$id = @$_GET['id'];

# $id = "opac-de-hil2:ppn:667363327"; # zum Testen
# $id = "opac-de-206:ppn:585004919";

if ($id) {
    if (preg_match('/^(opac-[^:]+):ppn:([0-9X]+)$/', $id, $match)) {

        $dbkey = $match[1];
        $config = $databases[ $dbkey ];
        if ($config) {
            $lbs = new LBS_Availability( $dbkey, $config );
        } else {
            $errors[] = "Unknown database";
        }

        $ppn   = $match[2];
        if (!LBS_Availability::validPPN($ppn)) {
            $ppn = "";
            $errors[] = "Invalid PPN!";
        }
    } else {
        $errors[] = "Invalid id format. Please use: {DBKEY}:ppn:{PPN} where DBKEY begins with 'opac-'";
    }
} else {
    $errors[] = "No identifier specified (parameter 'id')";
}

$docs = array();

if ($lbs and $ppn) {
    $rawavail = $lbs->getTitleinfoDOM($ppn, $language);
    $pica = $lbs->getPicaXML( $ppn );

    // Titel gefunden im Ausleihsystem
    if ($rawavail) {

        $root = $rawavail->documentElement;

        // <labels> entfernen, da unnötig
        $labels = $root->firstChild;
        if ($labels and $labels->tagName == "labels") $root->removeChild( $labels );

        // start converting: THIS IS THE CORE CONVERSION TO DAIA
 
        // XML processing in PHP is cruel - maybe XSLT is a better choice?
        # $xpath = new DOMXpath($rawavail);

        $availability = $root->getElementsByTagName('availability')->item(0);
        if ($availability) $availability = $availability->textContent;

        if ($availability >= 0) {
            $id = $lbs->getRecordURI( $ppn );
            $href = $lbs->getRecordLink( $ppn );

            if ( $id ) {
                $doc = new DAIA_Document( $id, $href );

                # TODO: Exemplardaten hinzufügen

                $docs[] = $doc;
            }
        }

        // END OF CONVERSION

    } else {
        $errors[] = "Record not found in the loan system";
        if ($pica) {

        }
    }

}

$daia = new DAIA( $docs );
if ($lbs) {
    $institution = new DAIA_Element( 
        $lbs->librarytitle, "http://uri.gbv.de/database/" . $lbs->dbkey, $lbs->homepage );
    $daia->setInstitution( $institution );
}

if ($errors) {
    foreach( $errors as $err ) {
        $daia->setMessage(new DAIA_Message($err, 'en', 100));
    }
}


/**
 * Construct and send Output in DAIA/XML
 */

header ("Content-Type:text/xml; charset=UTF-8");

$daiaxml = new DAIA_XML( $daia );
$dom     = $daiaxml->createNamespacedXml();

// for developing only
if ($rawavail) {
    $copy = $dom->importNode( $rawavail->documentElement, true );
    $dom->documentElement->appendChild( new DOMComment( "die interne LBS-API-Antwort, die auf DAIA gemappt werden muss" ) );
    $dom->documentElement->appendChild( $copy );
}

// for developing only
if ($pica) {
    $dom->documentElement->appendChild( new DOMComment( "Relevante Felder aus dem PICA/XML Lokaldatensatz" ) );
    foreach ($pica->documentElement->childNodes as $child) {
        if ($child->nodeType != 1 || $child->tagName != 'datafield') continue;
        $tag = $child->getAttribute('tag');#->value;
        // Feld 101@, 201@ und 209A werden ggf. benötigt für Standort-URIs
        if ($tag != '101@' && $tag != '201@' && $tag != '209A') continue; 
        $field = $dom->importNode( $child, true );
        $dom->documentElement->appendChild( $field );
    }
}

$xslt = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="daia.xsl"');
$dom->insertBefore( $xslt, $dom->documentElement );

print $dom->saveXml();

?>
