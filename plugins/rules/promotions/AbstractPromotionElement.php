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
namespace ZenMagick\plugins\rules\promotions;

use ZenMagick\StoreBundle\Model\Checkout\ShoppingCart;
use ZenMagick\StoreBundle\Entity\Account\Account;

/**
 * Base class for promotional elements.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
abstract class AbstractPromotionElement implements PromotionElement {
    private $product;
    private $shoppingCart;
    private $account;

    /**
     * Set the current product.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Product product The product.
     */
    public function setProduct($product) {
        $this->product = $product;
    }

    /**
     * Get the current product.
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The product or <code>null</code>.
     */
    public function getProduct() {
        return $this->product;
    }

    /**
     * Set the current shopping cart.
     *
     * @param ShoppingCart shoppingCart The shopping cart.
     */
    public function setShoppingCart(ShoppingCart $shoppingCart) {
        $this->shoppingCart = $shoppingCart;
    }

    /**
     * Get the current shopping cart.
     *
     * @return ShoppingCart The shopping cart.
     */
    public function getShoppingCart() {
        return $this->shoppingCart;
    }

    /**
     * Set the current account.
     *
     * @param ZenMagick\StoreBundle\Entity\Account\Account account The current account.
     */
    public function setAccount(Account $account) {
        $this->account = $account;
    }

    /**
     * Get the current account.
     *
     * @return ZenMagick\StoreBundle\Entity\Account\Account The current account.
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

