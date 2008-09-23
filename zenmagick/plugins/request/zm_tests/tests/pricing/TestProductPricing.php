<?php

/**
 * Test calculated product prices.
 *
 * <p>This test requires a vanilla demo store database setup.</p>
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestProductPricing extends ZMTestCase {
    protected $zen_cart_product_price_info;

    /**
     * Load expected prices.
     */
    public function setUp() {
    global $product_prices;
        $this->zen_cart_product_price_info = array();
        foreach (array(155, 156, 157, 159) as $id) {
            $pid = 'p'.$id;
            $this->zen_cart_product_price_info[$pid] = $product_prices[$pid];
        }
        $this->zen_cart_product_price_info = $product_prices;
    }

    /**
     * Test product price.
     */
    public function testProductPrice() {
        foreach ($this->zen_cart_product_price_info as $pid => $info) {
            $productId = (int)str_replace('p', '', $pid);
            $product = ZMProducts::instance()->getProductForId($productId);
            $this->assertNotNull($product);
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual($info['normal_price'], $offers->getBasePrice(false), '%s productId='.$productId);
        }
    }

    /**
     * Test special price.
     */
    public function testSpecialPrice() {
        foreach ($this->zen_cart_product_price_info as $pid => $info) {
            $productId = (int)str_replace('p', '', $pid);
            $product = ZMProducts::instance()->getProductForId($productId);
            $this->assertNotNull($product);
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual($info['special_price'], $offers->getSpecialPrice(false), '%s productId='.$productId);
        }
    }

    /**
     * Test sale price.
     */
    public function testSalePrice() {
        foreach ($this->zen_cart_product_price_info as $pid => $info) {
            $productId = (int)str_replace('p', '', $pid);
            $product = ZMProducts::instance()->getProductForId($productId);
            $this->assertNotNull($product);
            $offers = $product->getOffers();
            // without tax
            $this->assertEqual($info['sale_price'], $offers->getSalePrice(false), '%s productId='.$productId);
        }
    }

}

?>
