<?php
namespace apps\store\promotions;

/**
 * Base class for promotional elements.
 */
abstract class AbstractPromotionElement implements PromotionElement {
    private $shoppingCart;
    private $account;

    /**
     * Set the current shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     */
    public function setShoppingCart($shoppingCart) {
        $this->shoppingCart = $shoppingCart;
    }

    /**
     * Get the current shopping cart.
     *
     * @return ZMShoppingCart The shopping cart.
     */
    public function getShoppingCart() {
        return $this->shoppingCart;
    }

    /**
     * Set the current account.
     *
     * @param ZMAccount account The current account.
     */
    public function setAccount(ZMAccount $account) {
        $this->account = $account;
    }

    /**
     * Get the current account.
     *
     * @return ZMAccount The current account.
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterConfig() {
        return array();
    }

}

