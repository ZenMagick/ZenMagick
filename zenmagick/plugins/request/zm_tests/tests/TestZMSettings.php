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
        $key = '@@';
        $value = 'doh';
        $oldValue = ZMSettings::append($key, $value);
        $this->assertNull($oldValue);
        $this->assertEqual($value, ZMSettings::get($key));

        // and with delim
        $key = '@@@';
        $delim = '!';
        $oldValue = ZMSettings::append($key, $value, $delim);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($old.$value, ZMSettings::get($key));
    }

    /**
     * Test append old.
     */
    public function testAppendOld() {
        $key = '@@';
        $old = 'yo';
        ZMSettings::set($key, $old);
        $oldValue = ZMSettings::append($key, $value);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($old.$value, ZMSettings::get($key));

        // and with delim
        $key = '@@@';
        $delim = '!';
        ZMSettings::set($key, $old);
        $oldValue = ZMSettings::append($key, $value, $delim);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($old.$delim.$value, ZMSettings::get($key));
    }

}

?>
