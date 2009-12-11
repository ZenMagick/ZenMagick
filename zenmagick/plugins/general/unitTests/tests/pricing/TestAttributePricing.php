<?php

/**
 * Test calculated attribute prices.
 *
 * <p>This test requires a vanilla demo store database setup.</p>
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestAttributePricing extends ZMTestCase {
    protected $zen_cart_attribute_price_info;

    /**
     * Load expected prices.
     */
    public function setUp() {
    global $attribute_prices;
        $this->zen_cart_attribute_price_info = $attribute_prices;
    }

    /**
     * Test attribute price.
     */
    public function testValuePrice() {
        foreach (ZMProducts::instance()->getAllProducts(false) as $product) {
            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    $priceInfo = $this->zen_cart_attribute_price_info['p'.$value->getAttributeValueDetailsId()];
                    // default is 4 decimal digits...
                    $this->assertEqual((int)(10000*$priceInfo['dicount_price']), (int)(10000*$value->getPrice(false)), '%s productId='.$product->getId().' $valueId='.$value->getAttributeValueId().'/'.$value->getAttributeValueDetailsId());
                }
            }
        }
    }

    /**
     * Test one time price.
     */
    public function testValueOneTimePrice() {
        foreach (ZMProducts::instance()->getAllProducts(false) as $product) {
            foreach ($product->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $value) {
                    $zprice = zen_get_attributes_price_final_onetime($value->getAttributeValueDetailsId(), 1, '');
                    $this->assertEqual($zprice, $value->getOneTimePrice(false), '%s productId='.$product->getId().' $valueId='.$value->getAttributeValueId());
                }
            }
        }
    }

}

?>
