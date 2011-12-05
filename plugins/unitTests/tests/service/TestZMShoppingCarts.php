<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php

/**
 * Test cart service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMShoppingCarts extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function skip() {
        $account = $this->getRequest()->getAccount();
        if (null == $account) {
            $account = $this->container->get('accountService')->getAccountForId(1);
            $this->getRequest()->getSession()->setAccount($account);
        }
        $this->skipIf(null == $account || ZMAccount::REGISTERED != $account->getType(), 'Need to be logged in for this test');
    }


    /**
     * Get the account id to test.
     *
     * @return int An account id.
     */
    protected function getAccountId() {
        return $this->getRequest()->getAccountId();
    }

    /**
     * Dump cart.
     *
     * @param ZMShoppingCart shoppingCart The cart to dump.
     * @return string The dump.
     */
    protected function dumpCart($shoppingCart) {
        $html = $this->getRequest()->getToolbox()->html;
        $utils = $this->getRequest()->getToolbox()->utils;
        ob_start();
        foreach ($shoppingCart->getItems() as $item) {
            echo $item->getId().":".$html->encode($item->getName())."; qty=".$item->getQuantity().'; '.$utils->formatMoney($item->getItemPrice()).'/'.$utils->formatMoney($item->getItemTotal())."<BR>";
            if ($item->hasAttributes()) {
                foreach ($item->getAttributes() as $attribute) {
                    echo '&nbsp;&nbsp;'.$html->encode($attribute->getName()).":<BR>";
                    foreach ($attribute->getValues() as $value) {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp; *'.$html->encode($value->getName()).",<BR>";
                    }
                }
            }
        }
        return ob_get_clean();
    }

    /**
     * Test load cart.
     */
    public function testLoadCart() {
        $shoppingCart = $this->container->get('shoppingCartService')->loadCartForAccountId($this->getAccountId());
        $cartDump = $this->dumpCart($shoppingCart);
        $_SESSION['cart']->reset(false);
        $_SESSION['cart']->restore_contents();
        $loadedCartDump = $this->dumpCart($shoppingCart);
        $this->assertEqual($cartDump, $loadedCartDump);
    }

}
