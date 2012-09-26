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

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="customers_wishlist")
 * @ORM\Entity
 */
class Wishlist {
    /**
     * @var integer $productId
     *
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $productId;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $accountId;

    /**
     * @var string $productModel
     *
     * @ORM\Column(name="products_model", type="string", length=13, nullable=true)
     */
    private $productModel;

    /**
     * @var string $productName
     *
     * @ORM\Column(name="products_name", type="string", length=64, nullable=false)
     */
    private $productName;

    /**
     * @var float $productPrice
     *
     * @ORM\Column(name="products_price", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $productPrice;

    /**
     * @var float $finalPrice
     *
     * @ORM\Column(name="final_price", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $finalPrice;

    /**
     * @var integer $productQuantity
     *
     * @ORM\Column(name="products_quantity", type="integer", nullable=false)
     */
    private $productQuantity;

    /**
     * @var string $name
     *
     * @ORM\Column(name="wishlist_name", type="string", length=64, nullable=true)
     */
    private $name;


}
