<?php

/**
 * Test product.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMProduct.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TestZMProduct extends ZMTestCase {

    /**
     * Test existing manufacturer.
     */
    public function testExistingManufacturer() {
        $product = ZMProducts::instance()->getProductForId(36);
        if ($this->assertNotNull($product)) {
            $manufacturer = $product->getManufacturer();
            if ($this->assertNotNull($manufacturer)) {
                $this->assertEqual(9, $manufacturer->getId());
            }
        }
    }

    /**
     * Test missing manufacturer.
     */
    public function testMissingManufacturer() {
        $product = ZMProducts::instance()->getProductForId(31);
        if ($this->assertNotNull($product)) {
            $manufacturer = $product->getManufacturer();
            $this->assertNull($manufacturer);
        }
    }

    /**
     * Test null manufacturer.
     */
    public function testNULLManufacturer() {
        $product = ZMProducts::instance()->getProductForId(169);
        if ($this->assertNotNull($product)) {
            $manufacturer = $product->getManufacturer();
            $this->assertNull($manufacturer);
        }
    }

}

?>
