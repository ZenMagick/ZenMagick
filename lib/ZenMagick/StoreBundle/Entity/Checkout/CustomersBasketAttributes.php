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
 *
 * @ORM\Table(name="customers_basket_attributes",
 *  indexes={
 *      @ORM\Index(name="idx_cust_id_prod_id_zen", columns={"customers_id", "products_id"}),
 *  })
 * @ORM\Entity
 * @todo refactor with ShoppingCartItem
 */
class CustomersBasketAttributes
{
    /**
     * @var integer $cartAttributeId
     *
     * @ORM\Column(name="customers_basket_attributes_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cartAttributeId;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=false)
     */
    private $accountId;

    /**
     * @var string $skuId
     *
     * @ORM\Column(name="products_id", type="string", length=255, nullable=false)
     */
    private $skuId;

    /**
     * @var string $attributeId
     *
     * @ORM\Column(name="products_options_id", type="string", length=64, nullable=false)
     */
    private $attributeId;

    /**
     * @var integer $attributeValueId
     *
     * @ORM\Column(name="products_options_value_id", type="integer", nullable=false)
     */
    private $attributeValueId;

    /**
     * @var string $attributeValueText
     *
     * @ORM\Column(name="products_options_value_text", type="blob", nullable=true)
     */
    private $attributeValueText;

    /**
     * @var string $sortOrder
     *
     * @ORM\Column(name="products_options_sort_order", type="text", nullable=false)
     */
    private $sortOrder;

}
