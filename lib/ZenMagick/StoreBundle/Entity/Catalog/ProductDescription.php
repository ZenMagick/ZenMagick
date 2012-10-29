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
 * ZenMagick\StoreBundle\Entity\Catalog\ProductDescription
 *
 * @ORM\Table(name="products_description",
 *   indexes={
 *     @ORM\Index(name="idx_products_name_zen", columns={"products_name"}),
 * })
 * @ORM\Entity
 */
class ProductDescription
{
    /**
     * @var object $product
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="descriptions")
     * @ORM\JoinColumn(name="products_id", referencedColumnName="products_id")
     */
    private $product;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $languageId;

    /**
     * @var string $name
     *
     * @ORM\Column(name="products_name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="products_description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string $url
     *
     * @ORM\Column(name="products_url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var integer $productsViewed
     *
     * @ORM\Column(name="products_viewed", type="integer", nullable=true)
     */
    private $viewed;

    /**
     * Set productId
     *
     * @param  integer            $productId
     * @return ProductDescription
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set languageId
     *
     * @param  integer            $languageId
     * @return ProductDescription
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;

        return $this;
    }

    /**
     * Get languageId
     *
     * @return integer
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * Set name
     *
     * @param  string             $name
     * @return ProductDescription
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param  string             $description
     * @return ProductDescription
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param  string             $url
     * @return ProductDescription
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set viewed
     *
     * @param  integer            $viewed
     * @return ProductDescription
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * Get viewed
     *
     * @return integer
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * Set product
     *
     * @param  ZenMagick\StoreBundle\Entity\Catalog\Product $product
     * @return ProductDescription
     */
    public function setProduct(\ZenMagick\StoreBundle\Entity\Catalog\Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
