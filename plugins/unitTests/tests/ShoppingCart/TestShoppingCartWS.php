<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Test shopping cart via wrapper service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestShoppingCartWS extends ShoppingCartTestCaseBase
{
    /**
     * Compare values for the given productIds using the wrapper and service.
     */
    protected function compareValues_Wrapper_Service($ids)
    {
        $referenceCart = $this->getReferenceCart($ids);

        // load again from DB
        $serviceShoppingCart = $this->get('shoppingCartService')->loadCartForAccountId($this->getRequest()->getSession()->getAccountId());
        $itemMap = $serviceShoppingCart->getItems();

        // also validate against zencart cart
        $zcCart = $serviceShoppingCart->cart_;
        $zenReferenceItems = $zcCart->get_products();

        // zencart weight
        $this->assertEquals($zcCart->show_weight(), $serviceShoppingCart->getWeight());

        $itemTotal = 0;
        foreach ($referenceCart->getItems() as $item) {
            $itemTotals += $item->getQuantity();
            if ($this->assertTrue(array_key_exists($item->getId(), $itemMap), "%s: productId: ".$item->getId())) {
                // compare
                $serviceItem = $itemMap[$item->getId()];
                $this->assertEquals($item->getQuantity(), $serviceItem->getQuantity(), "%s: productId: ".$item->getId());
                // no tax
                $this->assertEquals($item->getItemPrice(false), $serviceItem->getItemPrice(false), "%s: productId: ".$item->getId());
                $this->assertEquals($item->getOneTimeCharge(false), $serviceItem->getOneTimeCharge(false), "%s: productId: ".$item->getId());

                // zencart prices
                foreach ($zenReferenceItems as $zi) {
                    if ($zi['id'] == $item->getId()) {
                        $this->assertEquals(round($item->getItemPrice(false), 2), round($zi['final_price'],2), "zc i: %s: productId: ".$item->getId());
                        $this->assertEquals(round($item->getOneTimeCharge(false), 2), round($zi['onetime_charges'],2), "zc ot: %s: productId: ".$item->getId());
                    }
                }

                // zencart quantities
                $itemId = $item->getId();
                $product = $item->getProduct();
                $this->assertEquals($zcCart->get_quantity($itemId), $referenceCart->getItemQuantityFor($itemId, false), "zc get_quantity: %s: productId: ".$item->getId());
                $this->assertEquals($zcCart->in_cart_mixed($itemId), $referenceCart->getItemQuantityFor($itemId, $product->isQtyMixed()), "zc in_cart_mixed: %s: productId: ".$item->getId());
            }
        }

        $this->assertEquals($zcCart->count_contents($itemId), $itemTotals);
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS_001_050()
    {
        $this->compareRange(1, 50, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS_051_100()
    {
        $this->compareRange(51, 100, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS_101_150()
    {
        $this->compareRange(101, 150, 'compareValues_Wrapper_Service');
    }

    /**
     * Test products comparing wrapper and service data.
     */
    public function testProductsWS_151_200()
    {
        $this->compareRange(151, 200, 'compareValues_Wrapper_Service');
    }

}
