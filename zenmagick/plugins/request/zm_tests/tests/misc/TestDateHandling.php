<?php

/**
 * Test date handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestDateHandling extends UnitTestCase {

    /**
     * Test parsing.
     */
    public function testParser() {
        $goodTests = array(
            // format, value, verification values
            array('dd-mm-yyyy', '19-12-2001', array('dd'=>19, 'mm'=>12, 'cc'=>20, 'yy'=>1, 'yyyy'=>2001)),
            array('ccyy/dd/mm:hh,ss,ii', '1978/13/02:19,45,18', array('dd'=>13, 'mm'=>2, 'cc'=>19, 'yy'=>78, 'yyyy'=>1978, 'hh'=>19,'ss'=>45,'ii'=>18)),
        );
        foreach ($goodTests as $test) {
            $token = ZMTools::parseDateString($test[1], $test[0]);
            foreach ($test[2] as $key => $value) {
                $this->assertEqual($value, $token[$key]);
            }
        }
    }

    /**
     * Test translating.
     */
    public function testTranslater() {
        $goodTests = array(
            // format, value, target, 'result
            array('dd-mm-yyyy', '19-12-2001', 'ccyy/mm/dd', '2001/12/19')
        );
        foreach ($goodTests as $test) {
            $result = ZMTools::translateDateString($test[1], $test[0], $test[2]);
            $this->assertEqual($test[3], $result);
        }
    }

}

?>
