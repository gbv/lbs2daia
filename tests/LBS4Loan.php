<?php

include 'lib/LBS4Loan.php';

class LBS4Loan_Test extends PHPUnit_Framework_TestCase {

    public function testURL() {
        $base = 'http://example.org/';
        $lbs  = new LBS4Loan( $base );
        $url  = $lbs->getTitleinfoURL( '12345' );
        $this->assertStringStartsWith( 'http://example.org/', $url );

    }
}

?>
