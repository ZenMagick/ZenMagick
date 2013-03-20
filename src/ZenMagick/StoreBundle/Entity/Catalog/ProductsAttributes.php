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

namespace ZenMagick\StoreBundle\Entity\Catalog;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\StoreBundle\Entity\ProductsAttributes
 *
 * @ORM\Table(name="products_attributes",
 *  indexes={
 *      @ORM\Index(name="idx_id_options_id_values_zen", columns={"products_id", "options_id","options_values_id"}),
 *      @ORM\Index(name="idx_opt_sort_order_zen", columns={"products_options_sort_order"}),
 *  })
 * @ORM\Entity
 * @todo integrate with ZMAttributeValue
 */
class ProductsAttributes
{
    /**
     * @var integer $productsAttributesId
     *
     * @ORM\Column(name="products_attributes_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $productsAttributesId;

    /**
     * @var integer $productsId
     *
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     */
    private $productsId;

    /**
     * @var integer $optionsId
     *
     * @ORM\Column(name="options_id", type="integer", nullable=false)
     */
    private $optionsId;

    /**
     * @var integer $optionsValuesId
     *
     * @ORM\Column(name="options_values_id", type="integer", nullable=false)
     */
    private $optionsValuesId;

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
     * @var integer $productsOptionsSortOrder
     *
     * @ORM\Column(name="products_options_sort_order", type="integer", nullable=false)
     */
    private $productsOptionsSortOrder;

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
     * @var boolean $attributesDisplayOnly
     *
     * @ORM\Column(name="attributes_display_only", type="boolean", nullable=false)
     */
    private $attributesDisplayOnly;

    /**
     * @var boolean $attributesDefault
     *
     * @ORM\Column(name="attributes_default", type="boolean", nullable=false)
     */
    private $attributesDefault;

    /**
     * @var boolean $attributesDiscounted
     *
     * @ORM\Column(name="attributes_discounted", type="boolean", nullable=false)
     */
    private $attributesDiscounted;

    /**
     * @var string $attributesImage
     *
     * @ORM\Column(name="attributes_image", type="string", length=64, nullable=true)
     */
    private $attributesImage;

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
     * @var boolean $attributesRequired
     *
     * @ORM\Column(name="attributes_required", type="boolean", nullable=false)
     */
    private $attributesRequired;

}
