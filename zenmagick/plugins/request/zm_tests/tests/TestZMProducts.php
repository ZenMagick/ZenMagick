<?php

/**
 * Test product service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMProducts extends UnitTestCase {

    /**
     * Set up.
     */
    public function setUp() {
        //ZMSettings::set('dbProvider', 'ZMCreoleDatabase');
        ZMSettings::set('dbProvider', 'ZMZenCartDatabase');
    }


    /**
     * Test create product.
     */
    public function testCreateProduct() {
        //TODO
    }

    /**
     * Test update product.
     */
    public function testUpdateProduct() {
        $product = ZMProducts::instance()->getProductForId(2);
        $this->assertNotNull($product);
        $product->set('qtyOrderUnits', 1);
        ZMProducts::instance()->updateProduct($product);
        $reloaded = ZMProducts::instance()->getProductForId($product->getId());
        foreach (array_keys(ZMDbTableMapper::instance()->getMapping(TABLE_PRODUCTS)) as $key) {
            $prefixList = array('get', 'is', 'has');
            $done = false;
            foreach ($prefixList as $prefix) {
                $getter = $prefix . $key;
                if (method_exists($product, $getter)) {
//echo 'testing: '.$key."<BR>";
                    $this->assertEqual($product->$getter(), $reloaded->$getter());
                    $done = true;
                    break;
                }
            }
            if (!$done) {
//echo 'testing prop: '.$key."<BR>";
                $this->assertEqual($product->get($key), $reloaded->get($key));
            }
        }
    }

}

?>
