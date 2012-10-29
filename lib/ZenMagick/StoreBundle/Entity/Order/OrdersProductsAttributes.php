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

namespace ZenMagick\StoreBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\StoreBundle\Entity\Order\OrdersProductsAttributes
 *
 * @ORM\Table(name="orders_products_attributes",
 *  indexes={
 *      @ORM\Index(name="idx_orders_id_prod_id_zen", columns={"orders_id", "orders_products_id"}),
 *  })
 * @ORM\Entity
 * @todo integrate with ZMAttributeValue
 */
class OrdersProductsAttributes
{
    /**
     * @var integer $ordersProductsAttributesId
     *
     * @ORM\Column(name="orders_products_attributes_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $ordersProductsAttributesId;

    /**
     * @var integer $ordersId
     *
     * @ORM\Column(name="orders_id", type="integer", nullable=false)
     */
    private $ordersId;

    /**
     * @var integer $ordersProductsId
     *
     * @ORM\Column(name="orders_products_id", type="integer", nullable=false)
     */
    private $ordersProductsId;

    /**
     * @var string $productsOptions
     *
     * @ORM\Column(name="products_options", type="string", length=32, nullable=false)
     */
    private $productsOptions;

    /**
     * @var string $productsOptionsValues
     *
     * @ORM\Column(name="products_options_values", type="text", nullable=false)
     */
    private $productsOptionsValues;

    /**
     * @var float $optionsValuesPrice
     *
     * @ORM\Column(name="options_values_price", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $optionsValuesPrice;

    /**
     * @var string $pricePrefix
     *
     * @ORM\Column(name="price_prefix", type="string", length=1, nullable=false)
     */
    private $pricePrefix;

    /**
     * @var boolean $productAttributeIsFree
     *
     * @ORM\Column(name="product_attribute_is_free", type="boolean", nullable=false)
     */
    private $productAttributeIsFree;

    /**
     * @var float $productsAttributesWeight
     *
     * @ORM\Column(name="products_attributes_weight", type="float", nullable=false)
     */
    private $productsAttributesWeight;

    /**
     * @var string $productsAttributesWeightPrefix
     *
     * @ORM\Column(name="products_attributes_weight_prefix", type="string", length=1, nullable=false)
     */
    private $productsAttributesWeightPrefix;

    /**
     * @var boolean $attributesDiscounted
     *
     * @ORM\Column(name="attributes_discounted", type="boolean", nullable=false)
     */
    private $attributesDiscounted;

    /**
     * @var boolean $attributesPriceBaseIncluded
     *
     * @ORM\Column(name="attributes_price_base_included", type="boolean", nullable=false)
     */
    private $attributesPriceBaseIncluded;

    /**
     * @var float $attributesPriceOnetime
     *
     * @ORM\Column(name="attributes_price_onetime", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceOnetime;

    /**
     * @var float $attributesPriceFactor
     *
     * @ORM\Column(name="attributes_price_factor", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceFactor;

    /**
     * @var float $attributesPriceFactorOffset
     *
     * @ORM\Column(name="attributes_price_factor_offset", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceFactorOffset;

    /**
     * @var float $attributesPriceFactorOnetime
     *
     * @ORM\Column(name="attributes_price_factor_onetime", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceFactorOnetime;

    /**
     * @var float $attributesPriceFactorOnetimeOffset
     *
     * @ORM\Column(name="attributes_price_factor_onetime_offset", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceFactorOnetimeOffset;

    /**
     * @var string $attributesQtyPrices
     *
     * @ORM\Column(name="attributes_qty_prices", type="text", nullable=true)
     */
    private $attributesQtyPrices;

    /**
     * @var string $attributesQtyPricesOnetime
     *
     * @ORM\Column(name="attributes_qty_prices_onetime", type="text", nullable=true)
     */
    private $attributesQtyPricesOnetime;

    /**
     * @var float $attributesPriceWords
     *
     * @ORM\Column(name="attributes_price_words", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceWords;

    /**
     * @var integer $attributesPriceWordsFree
     *
     * @ORM\Column(name="attributes_price_words_free", type="integer", nullable=false)
     */
    private $attributesPriceWordsFree;

    /**
     * @var float $attributesPriceLetters
     *
     * @ORM\Column(name="attributes_price_letters", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $attributesPriceLetters;

    /**
     * @var integer $attributesPriceLettersFree
     *
     * @ORM\Column(name="attributes_price_letters_free", type="integer", nullable=false)
     */
    private $attributesPriceLettersFree;

    /**
     * @var integer $productsOptionsId
     *
     * @ORM\Column(name="products_options_id", type="integer", nullable=false)
     */
    private $productsOptionsId;

    /**
     * @var integer $productsOptionsValuesId
     *
     * @ORM\Column(name="products_options_values_id", type="integer", nullable=false)
     */
    private $productsOptionsValuesId;

    /**
     * @var string $productsPrid
     *
     * @ORM\Column(name="products_prid", type="string", length=255, nullable=false)
     */
    private $productsPrid;

}
