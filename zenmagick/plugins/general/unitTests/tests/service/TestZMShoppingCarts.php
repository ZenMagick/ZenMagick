<?php

/**
 * Test cart service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMShoppingCarts extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function skip() {
        $account = $this->getRequest()->getAccount();
        $this->skipIf(null == $account || ZMZenCartUserSacsHandler::REGISTERED != $account->getType(), 'Need to be logged in for this test');
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
     */
    protected function dumpCart($shoppingCart) {
        $html = $this->getRequest()->getToolbox()->html;
        $utils = $this->getRequest()->getToolbox()->utils;
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
    }

    /**
     * Test load cart.
     */
    public function testLoadCart() {
        $shoppingCart = ZMShoppingCarts::instance()->loadCartForAccountId($this->getAccountId());
        $this->dumpCart($shoppingCart);
        echo '<hr>';
        $_SESSION['cart']->reset(false);
        $_SESSION['cart']->restore_contents();
        $this->dumpCart(new ZMShoppingCart());
    }

}

?>
