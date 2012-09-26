<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\StoreBundle\Entity\Checkout;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="customers_basket",
 *  indexes={
 *      @ORM\Index(name="idx_customers_id_zen", columns={"customers_id"}),
 *  })
 * @ORM\Entity
 * @todo refactor into ShoppingCartItem
 */
class CustomersBasket {
    /**
     * @var integer $cartId
     *
     * @ORM\Column(name="customers_basket_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cartId;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=false)
     */
    private $accountId;

    /**
     * @var string $skuId
     *
     * @ORM\Column(name="products_id", type="text", nullable=false)
     */
    private $skuId;

    /**
     * @var float $quantity
     *
     * @ORM\Column(name="customers_basket_quantity", type="float", nullable=false)
     */
    private $quantity;

    /**
     * @var float $calculatedPrice
     *
     * @ORM\Column(name="final_price", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $calculatedPrice;

    /**
     * @var string $dateAdded
     *
     * @ORM\Column(name="customers_basket_date_added", type="string", length=8, nullable=true)
     */
    private $dateAdded;


}
