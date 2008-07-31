<?php

/**
 * Test settings service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMSettings extends UnitTestCase {

    /**
     * Test append.
     */
    public function testAppendNew() {
        $key = 'n@@';
        $value = 'doh';
        $oldValue = ZMSettings::append($key, $value);
        $this->assertNull($oldValue);
        $this->assertEqual($value, ZMSettings::get($key));

        // and with delim
        $key = 'n@@@';
        $delim = '!';
        $oldValue = ZMSettings::append($key, $value, $delim);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($old.$value, ZMSettings::get($key));
    }

    /**
     * Test append old.
     */
    public function testAppendOld() {
        $key = 'o@@';
        $old = 'yo';
        ZMSettings::set($key, $old);
        $oldValue = ZMSettings::append($key, $value);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($old.$value, ZMSettings::get($key));

        // and with delim
        $key = 'o@@@';
        $delim = '!';
        ZMSettings::set($key, $old);
        $oldValue = ZMSettings::append($key, $value, $delim);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($old.$delim.$value, ZMSettings::get($key));
    }

}

?>
