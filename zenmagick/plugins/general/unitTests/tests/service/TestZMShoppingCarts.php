<?php

/**
 * Test cart service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMShoppingCarts.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TestZMShoppingCarts extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function skip() {
        $account = ZMRequest::instance()->getAccount();
        $this->skipIf(null == $account || ZMZenCartUserSacsHandler::REGISTERED != $account->getType(), 'Need to be logged in for this test');
    }


    /**
     * Get the account id to test.
     *
     * @return int An account id.
     */
    protected function getAccountId() {
        return ZMRequest::instance()->getAccountId();
    }

    /**
     * Dump cart.
     *
     * @param ZMShoppingCart cart The cart to dump.
     */
    protected function dumpCart($cart) {
        $html = ZMRequest::instance()->getToolbox()->html;
        $utils = ZMRequest::instance()->getToolbox()->utils;
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
        $cart = ZMShoppingCarts::instance()->loadCartForAccountId($this->getAccountId());
        $this->dumpCart($cart);
        echo '<hr>';
        $_SESSION['cart']->reset(false);
        $_SESSION['cart']->restore_contents();
        $this->dumpCart(new ZMShoppingCart());
    }

}

?>
