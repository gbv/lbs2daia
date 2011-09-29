<?php

/**
 * DAIA-Wrapper für PICA-Katalogdatenbanken und LBS-Systeme
 */

include 'lib/GBVOPACS.php';

include 'lib/phpDaia/daia.php'; // TODO: Ausgabe direkt als DAIA/XML wäre ggf. einfacher

// Sprache ('de' oder 'en')
$language = "de";


$opac = NULL;
$ppn  = NULL;
$pica = NULL;
$errors = array();
$rawavail = null;
$docs = array();


// Determine OPAC and PPN from query id
$id = @$_GET['id'];

# $id = "opac-de-hil2:ppn:667363327"; # zum Testen
# $id = "opac-de-206:ppn:585004919";

if ($id) {
    if (preg_match('/^([^:]+):ppn:([0-9X]+)$/', $id, $match)) {

        $dbkey = $match[1];

        $opac = getAvailabilityInterface( $dbkey );
        if (!$opac) $errors[] = "Unknown database";

        $ppn   = $match[2];
        if (!PICABase::validPPN($ppn)) {
            $ppn = "";
            $errors[] = "Invalid PPN!";
        }
    } else {
        $errors[] = "Invalid id format. Please use: {DBKEY}:ppn:{PPN} where DBKEY begins with 'opac-'";
    }
} else {
    $errors[] = "No identifier specified (parameter 'id')";
}


if ($opac and $ppn) {
    if ($opac->lbs4loan) {
        $rawavail = $opac->lbs4loan->getTitleinfoXML($ppn, $language);
    }
    if ($opac->picabase) {
        $pica = $opac->picabase->getPicaXML( $ppn );
    }

    // Titel gefunden im Ausleihsystem
    if ($rawavail) {
        $root = $rawavail;

        // <labels> entfernen, da unnötig
        $labels = $root->firstChild;
        if ($labels and $labels->tagName == "labels") $root->removeChild( $labels );

        // start converting: THIS IS THE CORE CONVERSION TO DAIA
 
        // XML processing in PHP is cruel - maybe XSLT is a better choice?
        # $xpath = new DOMXpath($rawavail);

        $availability = $root->getElementsByTagName('availability')->item(0);
        if ($availability) $availability = $availability->textContent;

        if ($availability >= 0) {
            $id = $opac->getRecordURI( $ppn );

            if ($opac->picabase) {
                $href = $opac->picabase->getRecordLink( $ppn );
            }

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

            # TODO: Ausleihstatus aus dem PICA-Datensatz ermitteln

        }
    }

}

$daia = new DAIA( $docs );
if ($opac) {
    $institution = new DAIA_Element( $opac->librarytitle, $opac->getDatabaseURI(), $opac->homepage );
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
    $copy = $dom->importNode( $rawavail, true );
    $dom->documentElement->appendChild( new DOMComment( "die interne LBS-API-Antwort, die auf DAIA gemappt werden muss" ) );
    $dom->documentElement->appendChild( $copy );
}

// for developing only
if ($pica) {
    $dom->documentElement->appendChild( new DOMComment( "Relevante Felder aus dem PICA/XML Lokaldatensatz" ) );
    foreach ($pica->childNodes as $child) {
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
