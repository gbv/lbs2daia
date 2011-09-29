<?php

include 'lib/PICABase.php';

class PICABase_Test extends PHPUnit_Framework_TestCase {

    public function testURL() {
        $dbkey = 'foo';
        $base  = 'http://example.org/DB=99/';
        $psi   = new PICABase( $dbkey, $base );
        $url   = $psi->getDatabaseURI();
        $this->assertEquals( $url, "http://uri.gbv.de/database/$dbkey" );
    }
}

?>
