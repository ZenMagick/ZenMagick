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
 * Test ZMBeanUtils.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMBeanUtils extends ZMTestCase {

    /**
     * Test obj2map.
     */
    public function testObj2map() {
        // get all properties
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj->set('foo', 'bar');
        $obj->set('doh', 'nut');
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectAll, $map);

        // get subset of properties
        $expectSpecific = array('foo' => 'bar', 'doh' => 'nut');
        $map = ZMBeanUtils::obj2map($obj, array_keys($expectSpecific));
        $this->assertEqual($expectSpecific, $map);

        // get subset of array
        $arr = array_merge($expectSpecific, array('some' => 'other'));
        $map = ZMBeanUtils::obj2map($arr, array_keys($expectSpecific));
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
        $obj = ZMBeanUtils::setAll($obj, $data);
        $this->assertEqual('bar',$obj->getFoo());
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectAll, $map);

        // set some
        $expectSome = array('foo' => 'bar', 'properties' => array('foo' => 'bar'), 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj = ZMBeanUtils::setAll($obj, $data, array('foo'));
        $this->assertEqual('bar',$obj->getFoo());
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectSome, $map);
    }

    /**
     * Test setAll with array
     */
    public function testSetAllArray() {
        // set all
        $map = array('foo' => 'bar', 'doh' => 'nut');
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'deng' => 'foo');
        $map = ZMBeanUtils::setAll($map, array('deng' => 'foo'));
        $this->assertEqual($expectAll, $map);
    }

    /**
     * Test map2obj.
     */
    public function testMap2obj() {
        $data = array('foo' => 'bar', 'doh' => 'nut');

        // test all
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = ZMBeanUtils::map2obj('ZMObject', $data);
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectAll, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test some
        $expectSome = array('foo' => 'bar', 'properties' => array('foo' => 'bar'), 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = ZMBeanUtils::map2obj('ZMObject', $data, array('foo'));
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectSome, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test getBean.
     */
    public function testGetBean() {
        $expect = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $definition = 'ZMObject#foo=bar&doh=nut';
        $obj = ZMBeanUtils::getBean($definition);
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test empty properties
        $expect = array('properties' => array(), 'propertyNames' => array(), 'attachedMethods' => array());
        $definition = 'ZMObject';
        $obj = ZMBeanUtils::getBean($definition);
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test bean::
     */
    public function testMagicBean() {
        // test null bean
        $this->assertEqual(null, ZMBeanUtils::getBean('bean::null'));

        // test property bean
        $bean = ZMBeanUtils::getBean('ZMObject#someBean=bean::Address');
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
        $ref = ZMBeanUtils::getBean('ref::ZMProducts#foo=bar');
        if ($this->assertNotNull($ref)) {
            $this->assertTrue($ref instanceof ZMProducts);
            // now test that we actually got the singleton
            $foo = ZMProducts::instance()->getFoo();
            $this->assertEqual('bar', $foo);
        }
    }

    /**
     * Test magic value
     */
    public function testMagicValue() {
        $bean = ZMBeanUtils::getBean('ZMObject#handler='.urlencode('bean::ZMProduct#name=foo'));
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
