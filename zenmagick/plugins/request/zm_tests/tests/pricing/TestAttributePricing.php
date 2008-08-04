<?php

/**
 * Test calculated attribute prices.
 *
 * <p>This test requires a vanilla demo store database setup.</p>
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestAttributePricing extends UnitTestCase {
    protected $zen_cart_product_price_info;
    protected $zen_cart_attribute_price_info;

    /**
     * Load expected prices.
     */
    public function setUp() {
    global $product_prices, $attribute_prices;
        $this->zen_cart_product_price_info = $product_prices;
        $this->zen_cart_attribute_price_info = $attribute_prices;
    }

    /**
     * Test attribute price.
     */
    public function testAttributeValuePrice() {
        //$ids = array(173, 36, 100, 74, 61, 175, 178); foreach ($ids as $pid) {
        //$ids = array(155, 156, 157, 159); foreach ($ids as $pid) {
        //$ids = array(159); foreach ($ids as $pid) {
        //$ids = array(2); foreach ($ids as $pid) {
        foreach ($this->zen_cart_product_price_info as $pid => $info) {
            $productId = (int)str_replace('p', '', $pid);
            $product = ZMProducts::instance()->getProductForId($productId);
            $this->assertNotNull($product);

            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    $priceInfo = $this->zen_cart_attribute_price_info['p'.$value->getId()];
                    // default is 4 decimal digits...
                    $this->assertEqual((int)(10000*$priceInfo['dicount_price']), (int)(10000*$value->getPrice(false)), '%s productId='.$productId.' $valueId='.$value->getId());
                }
            }
        }
    }

    /**
     * Test one time price.
     */
    public function testAttributeValueOneTimePrice() {
        //$ids = array(173, 36, 100, 74, 61, 175, 178); foreach ($ids as $pid) {
        //$ids = array(2); foreach ($ids as $pid) {
        foreach ($this->zen_cart_product_price_info as $pid => $info) {
            $productId = (int)str_replace('p', '', $pid);
            $product = ZMProducts::instance()->getProductForId($productId);
            $this->assertNotNull($product);

            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    $zprice = zen_get_attributes_price_final_onetime($value->getId(), 1, '');
                    $this->assertEqual($zprice, $value->getOneTimePrice(false), '%s productId='.$productId.' $valueId='.$value->getId());
                }
            }
        }
    }

}

?>
