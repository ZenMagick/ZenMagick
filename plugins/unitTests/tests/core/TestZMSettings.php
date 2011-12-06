<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php

/**
 * Test settings service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMSettings extends ZMTestCase {

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
        // ',' is the default delimiter
        $this->assertEqual($old.','.$value, ZMSettings::get($key));

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
