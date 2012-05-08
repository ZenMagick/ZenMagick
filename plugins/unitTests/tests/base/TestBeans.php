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

use zenmagick\base\Beans;
use zenmagick\base\ZMObject;
use zenmagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test Beans.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestBeans extends TestCase {

    /**
     * Test obj2map.
     */
    public function testObj2map() {
        // get all properties
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj->set('foo', 'bar');
        $obj->set('doh', 'nut');
        $map = Beans::obj2map($obj);
        $this->assertEqual($expectAll, $map);

        // get subset of properties
        $expectSpecific = array('foo' => 'bar', 'doh' => 'nut');
        $map = Beans::obj2map($obj, array_keys($expectSpecific));
        $this->assertEqual($expectSpecific, $map);

        // get subset of array
        $arr = array_merge($expectSpecific, array('some' => 'other'));
        $map = Beans::obj2map($arr, array_keys($expectSpecific));
        $this->assertEqual($expectSpecific, $map);
    }

    /**
     * Test setAll.
     */
    public function testSetAll() {
        $data = array('foo' => 'bar', 'doh' => 'nut');

        // set all
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj = Beans::setAll($obj, $data);
        $this->assertEqual('bar',$obj->getFoo());
        $map = Beans::obj2map($obj);
        $this->assertEqual($expectAll, $map);

        // set some
        $expectSome = array('foo' => 'bar', 'properties' => array('foo' => 'bar'), 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj = Beans::setAll($obj, $data, array('foo'));
        $this->assertEqual('bar',$obj->getFoo());
        $map = Beans::obj2map($obj);
        $this->assertEqual($expectSome, $map);
    }

    /**
     * Test setAll with array
     */
    public function testSetAllArray() {
        // set all
        $map = array('foo' => 'bar', 'doh' => 'nut');
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'deng' => 'foo');
        $map = Beans::setAll($map, array('deng' => 'foo'));
        $this->assertEqual($expectAll, $map);
    }

    /**
     * Test map2obj.
     */
    public function testMap2obj() {
        $data = array('foo' => 'bar', 'doh' => 'nut');

        // test all
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = Beans::map2obj('zenmagick\base\ZMObject', $data);
        $map = Beans::obj2map($obj);
        $this->assertEqual($expectAll, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test some
        $expectSome = array('foo' => 'bar', 'properties' => array('foo' => 'bar'), 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = Beans::map2obj('zenmagick\base\ZMObject', $data, array('foo'));
        $map = Beans::obj2map($obj);
        $this->assertEqual($expectSome, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test getBean.
     */
    public function testGetBean() {
        $expect = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $definition = 'zenmagick\base\ZMObject#foo=bar&doh=nut';
        $obj = Beans::getBean($definition);
        $map = Beans::obj2map($obj);
        $this->assertEqual($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test empty properties
        $expect = array('properties' => array(), 'propertyNames' => array(), 'attachedMethods' => array());
        $definition = 'zenmagick\base\ZMObject';
        $obj = Beans::getBean($definition);
        $map = Beans::obj2map($obj);
        $this->assertEqual($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test bean::
     */
    public function testMagicBean() {
        // test null bean
        $this->assertEqual(null, Beans::getBean('bean::null'));

        // test property bean
        $bean = Beans::getBean('zenmagick\base\ZMObject#someBean=bean::ZMAddress');
        if ($this->assertNotNull($bean) && $this->assertTrue($bean instanceof ZMObject)) {
            $someBean = $bean->getSomeBean();
            if ($this->assertNotNull($someBean)) {
                $this->assertTrue($someBean instanceof ZMAddress);
            }
        }
    }

    /**
     * Test ref::
     */
    public function testMagicRef() {
        $ref = Beans::getBean('ref::productService#foo=bar');
        if ($this->assertNotNull($ref)) {
            $this->assertTrue($ref instanceof ZMProducts);
            // now test that we actually got the singleton
            $foo = $this->container->get('productService')->getFoo();
            $this->assertEqual('bar', $foo);
        }
    }

    /**
     * Test magic value
     */
    public function testMagicValue() {
        $bean = Beans::getBean('zenmagick\base\ZMObject#handler='.urlencode('bean::ZMProduct#name=foo'));
        if ($this->assertNotNull($bean)) {
            $handler = $bean->getHandler();
            if ($this->assertNotNull($handler)) {
                if ($this->assertTrue($handler instanceof ZMProduct)) {
                    $this->assertEqual('foo', $handler->getName());
                }
            }
        }
    }

}
