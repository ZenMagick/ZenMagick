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
class AttributePricing extends UnitTestCase {
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
     * Test product price.
     */
    public function testFinalPrice() {
        //$ids = array(173, 36, 100, 74, 61, 175, 178); foreach ($ids as $pid) {
        foreach ($this->zen_cart_product_price_info as $pid => $info) {
            $productId = (int)str_replace('p', '', $pid);
            $product = ZMProducts::instance()->getProductForId($productId);
            $this->assertNotNull($product);

            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    $priceInfo = $this->zen_cart_attribute_price_info['p'.$value->getId()];
                    // case to (string) to make things like 83.3333 compare...
                    $this->assertEqual((int)(10000*$priceInfo['dicount_price']), (int)(10000*$value->getPrice(false)), '%s productId='.$productId.' $valueId='.$value->getId());
                }
            }
        }
    }

}

?>
