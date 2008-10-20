<?php

/**
 * Test offers.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMOffers extends ZMTestCase {

    /**
     * Test quantity discounts
     */
    public function testQtyDiscounts() {
        $product = ZMProducts::instance()->getProductForId(176);
        if ($this->assertNotNull($product)) {
            $offers = $product->getOffers();
            if ($this->assertNotNull($offers)) {
                $discounts = $offers->getQuantityDiscounts();
                $this->assertTrue(is_array($discounts));
                $this->assertEqual(5, count($discounts));
                // grab one and check details
                $discount = $discounts[3];
                $this->assertEqual(176, $discount->getProductId());
                $this->assertEqual(48, $discount->getQuantity());
                $this->assertEqual(30.00, $discount->getValue());
                $this->assertEqual(77.00, $discount->getPrice());
            }
        }
    }

}

?>
