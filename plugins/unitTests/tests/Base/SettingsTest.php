<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\Base\Settings;
use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test settings
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SettingsTest extends BaseTestCase
{
    /**
     * Test append.
     */
    public function testAppendNew()
    {
        $settings = new Settings();
        $key = 'a.b.n@@';
        $value = 'doh';
        $oldValue = $settings->append($key, $value);
        $this->assertNull($oldValue);
        $this->assertEquals($value, $settings->get($key));

        // and with delim
        $key = 'b.c.n@@@';
        $delim = '!';
        $old = null;
        $oldValue = $settings->append($key, $value, $delim);
        $this->assertEquals($old, $oldValue);
        $this->assertEquals($value, $settings->get($key));
    }

    /**
     * Test append multiple.
     */
    public function testAppendMultiple()
    {
        $settings = new Settings();
        $key = 'a.b.c.o@@@@';
        $old = null;

        $oldValue = $settings->append($key, 'yo', ',');
        $this->assertEquals(null, $oldValue);
        $this->assertEquals('yo', $settings->get($key));

        $oldValue = $settings->append($key, 'yo', ',');
        $this->assertEquals('yo', $oldValue);
        $this->assertEquals('yo,yo', $settings->get($key));

        $oldValue = $settings->append($key, 'yo', ',');
        $this->assertEquals('yo,yo', $oldValue);
        $this->assertEquals('yo,yo,yo', $settings->get($key));
    }

    /**
     * Test add new value.
     */
    public function testAddNewValue()
    {
        $settings = new Settings();
        $key = 'a.b.n@@';
        $value = 'doh';

        $this->assertFalse($settings->exists($key));
        $oldValue = $settings->add($key, $value);
        $this->assertNull($oldValue);
        $this->assertEquals(array($value), $settings->get($key));
    }

    /**
     * Test add existing value.
     */
    public function testAddExistingValue()
    {
        $settings = new Settings();
        $key = 'a.b.n@@';
        $value = 'doh';
        $nextValue = 'bah';

        // set first value
        $settings->set($key, $value);
        $this->assertEquals($value, $settings->get($key));

        $oldValue = $settings->add($key, $nextValue);
        $this->assertEquals($value, $oldValue);
        $this->assertEquals(array($value, $nextValue), $settings->get($key));
    }

    /**
     * Test new value.
     */
    public function testNewValue()
    {
        $settings = new Settings();
        $key = 'a.b.n@@';
        $pkey = 'a.b';
        $value = 'doh';

        $this->assertFalse($settings->exists($key));
        $oldValue = $settings->set($key, $value);
        $this->assertNull($oldValue);
        $this->assertEquals($value, $settings->get($key));
        $this->assertEquals(array('n@@' => $value), $settings->get($pkey));
    }

    /**
     * Test update value.
     */
    public function testUpdateValue()
    {
        $settings = new Settings();
        $key = 'a.b.n@@';
        $value = 'doh';
        $newValue = 'deng';

        $settings->set($key, $value);
        $this->assertEquals($value, $settings->get($key));
        $oldValue = $settings->set($key, $newValue);
        $this->assertEquals($value, $oldValue);
        $this->assertEquals($newValue, $settings->get($key));
    }

    /**
     * Test slash.
     */
    public function testSlash()
    {
        $settings = new Settings();
        $skey = 'a/b/n@@';
        $dkey = 'a.b.n@@';
        $value = 'doh';
        $newValue = 'deng';

        $settings->set($dkey, $value);
        $this->assertEquals($value, $settings->get($dkey));
        $this->assertEquals($value, $settings->get($skey));
        $oldValue = $settings->set($skey, $newValue);
        $this->assertEquals($value, $oldValue);
        $this->assertEquals($newValue, $settings->get($dkey));
        $this->assertEquals($newValue, $settings->get($skey));
    }

    /**
     * Test strtok.
     * TODO: move into toolbox test
     */
    public function testStrtok()
    {
        $path = 'a.b.c.d.e';
        $explodeElements = explode('.', $path);
        $this->assertEquals($explodeElements, Toolbox::mexplode('./', $path));
    }

}
