<?php

/**
 * Test range parsing.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestRangeParser extends ZMTestCase {

    /**
     * Test.
     */
    public function test() {
        $tests = array(
            array('3', array(3=>3)),
            array('3,4-7', array(3=>3,4=>4,5=>5,6=>6,7=>7)),
            array('2,4,6-7', array(2=>2,4=>4,6=>6,7=>7)),
            array('6-7', array(6=>6,7=>7)),
            array('6-7,8-10', array(6=>6,7=>7,8=>8,9=>9,10=>10)),
        );
        foreach ($tests as $test) {
            $this->assertEqual($test[1], ZMTools::parseRange($test[0]));
        }
    }

}

?>
