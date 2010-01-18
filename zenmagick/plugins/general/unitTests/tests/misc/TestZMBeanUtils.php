<?php

/**
 * Test ZMBeanUtils.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMBeanUtils extends ZMTestCase {

    /**
     * Test obj2map.
     */
    public function testObj2map() {
        // get all properties
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
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
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = new ZMObject();
        $obj = ZMBeanUtils::setAll($obj, $data);
        $this->assertEqual('bar',$obj->getFoo());
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectAll, $map);

        // set some
        $expectSome = array('foo' => 'bar', 'propertyNames' => array('foo'), 'attachedMethods' => array());
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
        $expectAll = array('foo' => 'bar', 'doh' => 'nut', 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $obj = ZMBeanUtils::map2obj('ZMObject', $data);
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectAll, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test some
        $expectSome = array('foo' => 'bar', 'propertyNames' => array('foo'), 'attachedMethods' => array());
        $obj = ZMBeanUtils::map2obj('ZMObject', $data, array('foo'));
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expectSome, $map);
        $this->assertTrue($obj instanceof ZMObject);
    }

    /**
     * Test getBean.
     */
    public function testGetBean() {
        $expect = array('foo' => 'bar', 'doh' => 'nut', 'propertyNames' => array('foo', 'doh'), 'attachedMethods' => array());
        $definition = 'ZMObject#foo=bar&doh=nut';
        $obj = ZMBeanUtils::getBean($definition);
        $map = ZMBeanUtils::obj2map($obj);
        $this->assertEqual($expect, $map);
        $this->assertTrue($obj instanceof ZMObject);

        // test empty properties
        $expect = array('propertyNames' => array(), 'attachedMethods' => array());
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
        $ref = ZMBeanUtils::getBean('ref::Products#foo=bar');
        if ($this->assertNotNull($ref)) {
            $this->assertTrue($ref instanceof ZMProducts);
            // now test that we actually got the singleton
            $foo = ZMProducts::instance()->getFoo();
            $this->assertEqual('bar', $foo);
        }
    }

    /**
     * Test set::
     */
    public function testMagicSet() {
        ZMSettings::set('setset', 'ZMObject#foo=bar');
        $set = ZMBeanUtils::getBean('set::setset');
        if ($this->assertNotNull($set)) {
            $this->assertTrue($set instanceof ZMObject);
            $this->assertEqual('bar', $set->getFoo());
        }
    }

    /**
     * Test magic value
     */
    public function testMagicValue() {
        $bean = ZMBeanUtils::getBean('ZMObject#handler='.urlencode('bean::Product#name=foo'));
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

?>
