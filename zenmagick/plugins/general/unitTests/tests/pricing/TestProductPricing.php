<?php

/**
 * Test calculated product prices.
 *
 * <p>This test requires a vanilla demo store database setup.</p>
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestProductPricing extends ZMTestCase {

    /**
     * Test product base price.
     */
    public function testBasePrice() {
        foreach (ZMProducts::instance()->getAllProducts(false) as $product) {
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual(zen_get_products_base_price($product->getId()), $offers->getBasePrice(false), '%s productId='.$product->getId());
        }
    }

    /**
     * Test special price.
     */
    public function testSpecialPrice() {
        foreach (ZMProducts::instance()->getAllProducts(false) as $product) {
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual(zen_get_products_special_price($product->getId(), true), $offers->getSpecialPrice(false), '%s productId='.$product->getId());
        }
    }

    /**
     * Test sale price.
     */
    public function testSalePrice() {
        foreach (ZMProducts::instance()->getAllProducts(false) as $product) {
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual(zen_get_products_special_price($product->getId(), false), $offers->getSalePrice(false), '%s productId='.$product->getId());
        }
    }

}

?>
