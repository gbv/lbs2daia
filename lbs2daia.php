<?php

$config = array(
    "lbsbase" => "http://opac.lbs-hildesheim.gbv.de:9090/",
    "dbkey"   => "opac-de-hil2",
);

include 'lib/LBS_Availability.php';
include 'lib/phpDaia/daia.php';

#include_once 'pica2daia.php';

$lbs = new LBS_Availability( $config );

$ppn = @$_GET['ppn'];

/**
 *
 */

$language = "de"; // 'de' oder 'en';

/*
$ppns = array(
    '660851474',
#    '647679477',
#    '665516037',
#    '664334679',
#    '668108274',
#    '16175468'
);

#$xml = file_get_contents( 'examples/660851474.xml' );

foreach ($ppns as $ppn ) {
#$ppn = "660851474";
    $dom = $lbs->getTitleinfoDOM( $ppn, 'de' );

$name = 'titleReservationAllowed';
#$ppn = $xml->titleReservationAllowed;
#ttributes('ppn')->asXML();

#$dom->loadXML($xml->asXML());

file_put_contents("examples/$ppn.xml", $dom->saveXML());
}
*/

$doc = new DAIA_PICA(array($_REQUEST['ppn']));
$daia = new DAIA_Document( $id, $href );
 $daia->setMessage(new DAIA_Message("PPN not found!", 'en', 100));


# this implementation only supports XML format

header ("Content-Type:text/xml; charset=UTF-8");


echo $daia->toXml();

?>
