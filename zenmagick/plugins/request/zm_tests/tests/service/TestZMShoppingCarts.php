<?php

/**
 * Test cart service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMShoppingCarts extends ZMTestCase {

    /**
     * Dump cart.
     *
     * @param ZMShoppingCart cart The cart to dump.
     */
    protected function dumpCart($cart) {
        $html = ZMToolbox::instance()->html;
        $utils = ZMToolbox::instance()->utils;
        foreach ($cart->getItems() as $item) {
            echo $item->getId().":".$html->encode($item->getName(), false)."; qty=".$item->getQuantity().'; '.$utils->formatMoney($item->getItemPrice(), true, false).'/'.$utils->formatMoney($item->getItemTotal(), true, false)."<BR>";
            if ($item->hasAttributes()) {
                foreach ($item->getAttributes() as $attribute) {
                    echo '&nbsp;&nbsp;'.$html->encode($attribute->getName(), false).":<BR>";
                    foreach ($attribute->getValues() as $value) {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp; *'.$html->encode($value->getName(), false).",<BR>";
                    }
                }
            }
        }
    }

    /**
     * Test load cart.
     */
    public function testLoadCart() {
        $cart = ZMShoppingCarts::instance()->loadCartForAccountId(90);
        $this->dumpCart($cart);
        echo '<hr>';
        $_SESSION['cart']->reset(false);
        $_SESSION['cart']->restore_contents();
        $this->dumpCart(new ZMShoppingCart());
    }

}

?>
