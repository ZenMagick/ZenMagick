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

use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;
use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test Beans.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BeansTest extends BaseTestCase
{
    /**
     * Test obj2map.
     */
    public function testObj2map()
    {
        // get all properties
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj->set('foo', 'bar');
        $obj->set('doh', 'nut');
        $map = Beans::obj2map($obj);
        $this->assertEquals($expectAll, $map);

        // get subset of properties
        $expectSpecific = array('foo' => 'bar', 'doh' => 'nut');
        $map = Beans::obj2map($obj, array_keys($expectSpecific));
        $this->assertEquals($expectSpecific, $map);

        // get subset of array
        $arr = array_merge($expectSpecific, array('some' => 'other'));
        $map = Beans::obj2map($arr, array_keys($expectSpecific));
        $this->assertEquals($expectSpecific, $map);
    }

    /**
     * Test setAll.
     */
    public function testSetAll()
    {
        $data = array('foo' => 'bar', 'doh' => 'nut');

        // set all
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj = Beans::setAll($obj, $data);
        $this->assertEquals('bar',$obj->getFoo());
        $map = Beans::obj2map($obj);
        $this->assertEquals($expectAll, $map);

        // set some
        $expectSome = array('foo' => 'bar', 'properties' => array('foo' => 'bar'), 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj = Beans::setAll($obj, $data, array('foo'));
        $this->assertEquals('bar',$obj->getFoo());
        $map = Beans::obj2map($obj);
        $this->assertEquals($expectSome, $map);
    }

    /**
     * Test setAll with array
     */
    public function testSetAllArray()
    {
        // set all
        $map = array('foo' => 'bar', 'doh' => 'nut');
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'deng' => 'foo');
        $map = Beans::setAll($map, array('deng' => 'foo'));
        $this->assertEquals($expectAll, $map);
    }

    /**
     * Test map2obj.
     */
    public function testMap2obj()
    {
        $data = array('foo' => 'bar', 'doh' => 'nut');

        // test all
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = Beans::map2obj('ZenMagick\Base\ZMObject', $data);
        $map = Beans::obj2map($obj);
        $this->assertEquals($expectAll, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test some
        $expectSome = array('foo' => 'bar', 'properties' => array('foo' => 'bar'), 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = Beans::map2obj('ZenMagick\Base\ZMObject', $data, array('foo'));
        $map = Beans::obj2map($obj);
        $this->assertEquals($expectSome, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test getBean.
     */
    public function testGetBean()
    {
        $expect = array('foo' => 'bar', 'doh' => 'nut', 'properties' => array('foo' => 'bar', 'doh' => 'nut'), 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $definition = 'ZenMagick\Base\ZMObject#foo=bar&doh=nut';
        $obj = Beans::getBean($definition);
        $map = Beans::obj2map($obj);
        $this->assertEquals($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test empty properties
        $expect = array('properties' => array(), 'propertyNames' => array(), 'attachedMethods' => array());
        $definition = 'ZenMagick\Base\ZMObject';
        $obj = Beans::getBean($definition);
        $map = Beans::obj2map($obj);
        $this->assertEquals($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test bean::
     */
    public function testMagicBean()
    {
        // test null bean
        $this->assertEquals(null, Beans::getBean('bean::null'));

        // test property bean
        $bean = Beans::getBean('ZenMagick\Base\ZMObject#someBean=bean::ZenMagick\StoreBundle\Entity\Address');
        if ($this->assertNotNull($bean) && $this->assertTrue($bean instanceof ZMObject)) {
            $someBean = $bean->getSomeBean();
            if ($this->assertNotNull($someBean)) {
                $this->assertTrue($someBean instanceof \ZenMagick\StoreBundle\Entity\Address);
            }
        }
    }

    /**
     * Test ref::
     */
    public function testMagicRef()
    {
        $ref = Beans::getBean('ref::productService#foo=bar');
        if ($this->assertNotNull($ref)) {
            $this->assertTrue($ref instanceof ZenMagick\StoreBundle\Services\Products);
            // now test that we actually got the singleton
            $foo = $this->get('productService')->getFoo();
            $this->assertEquals('bar', $foo);
        }
    }

    /**
     * Test magic value
     */
    public function testMagicValue()
    {
        $bean = Beans::getBean('ZenMagick\Base\ZMObject#handler='.urlencode('bean::ZenMagick\StoreBundle\Entity\Product#name=foo'));
        if ($this->assertNotNull($bean)) {
            $handler = $bean->getHandler();
            if ($this->assertNotNull($handler)) {
                if ($this->assertTrue($handler instanceof ZenMagick\StoreBundle\Entity\Product)) {
                    $this->assertEquals('foo', $handler->getName());
                }
            }
        }
    }

}
