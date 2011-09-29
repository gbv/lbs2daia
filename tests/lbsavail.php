<?php

include 'lib/GBVOPACS.php';

class Availability_Test extends PHPUnit_Framework_TestCase {

    public function testConfig() {
        $dbkey = "opac-de-hil2";
        $opac = getAvailabilityInterface( $dbkey );
        $this->assertTrue( !!$opac );
    }
}

?>
