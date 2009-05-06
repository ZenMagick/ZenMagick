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
     * Test load cart.
     */
    public function testLoadCart() {
        $html = ZMToolbox::instance()->html;
        $utils = ZMToolbox::instance()->utils;
        $cart = ZMShoppingCarts::instance()->loadCartForAccountId(90);
        foreach ($cart->getItems() as $item) {
            echo $item->getId().":".$html->encode($item->getName(), false)."; qty=".$item->getQuantity().'; '.$utils->formatMoney($item->getItemTotal(), true, false)."<BR>";
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

}

?>
