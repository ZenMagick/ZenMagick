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
        $old = null;
        $oldValue = ZMSettings::append($key, $value, $delim);
        $this->assertEqual($old, $oldValue);
        $this->assertEqual($value, ZMSettings::get($key));
    }

    /**
     * Test append old.
     */
    public function testAppendOld() {
        $key = 'o@@';
        $old = 'yo';
        $value = 'doh';
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

    /**
     * Test append multiple.
     */
    public function testAppendMultiple() {
        $key = 'o@@@@';
        $old = null;

        $oldValue = ZMSettings::append($key, 'yo', ',');
        $this->assertEqual(null, $oldValue);
        $this->assertEqual('yo', ZMSettings::get($key));

        $oldValue = ZMSettings::append($key, 'yo', ',');
        $this->assertEqual('yo', $oldValue);
        $this->assertEqual('yo,yo', ZMSettings::get($key));

        $oldValue = ZMSettings::append($key, 'yo', ',');
        $this->assertEqual('yo,yo', $oldValue);
        $this->assertEqual('yo,yo,yo', ZMSettings::get($key));
    }

}

?>
