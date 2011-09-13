<?php

include 'lib/LBS_Availability.php';

class LBS_Availability_Test extends PHPUnit_Framework_TestCase {

    public function testConfig() {
        $config = array(
            "lbsbase"  => "http://example.org/",
            "dbkey"    => "foobar",
            "picabase" => "htp://example.com/",
            "librarytitle" => "A simple library",
        );
        $lbs = new LBS_Availability( $config );
        $url = $lbs->getTitleinfoURL( '12345' );
        $this->assertStringStartsWith( 'http://example.org/', $url );

    }
}

?>
