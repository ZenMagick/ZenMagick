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
        ZMSettings::set('dbProvider', 'ZMCreoleDatabase');
        //ZMSettings::set('dbProvider', 'ZMZenCartDatabase');
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
        $product->setName($product->getName().'@@@');
        ZMProducts::instance()->updateProduct($product);
        $reloaded = ZMProducts::instance()->getProductForId($product->getId());
        foreach (array_keys(ZMDbTableMapper::instance()->getMapping(array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION))) as $key) {
            $prefixList = array('get', 'is', 'has');
            $done = false;
            foreach ($prefixList as $prefix) {
                $getter = $prefix . ucwords($key);
                if (method_exists($product, $getter)) {
                    $this->assertEqual($product->$getter(), $reloaded->$getter(), '%s getter='.$getter);
                    $done = true;
                    break;
                }
            }
            if (!$done) {
                $this->assertEqual($product->get($key), $reloaded->get($key), '%s key='.$key);
            }
        }
        // revert name change
        $product->setName(str_replace('@@@', '', $product->getName()));
        ZMProducts::instance()->updateProduct($product);
    }

}

?>
