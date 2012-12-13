<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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

use ZenMagick\Base\ZMObject;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Product Type Configuration
 *
 * @ORM\Table(name="product_type_layout",
 *  indexes={
 *      @ORM\Index(name="unq_config_key_zen", columns={"configuration_key"}),
 *      @ORM\Index(name="idx_type_id_sort_order_zen", columns={"product_type_id", "sort_order"}),
 * })
 * @ORM\Entity
 */
class ProductTypeLayout extends ZMObject
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="configuration_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var text $title
     *
     * @ORM\Column(name="configuration_title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string $key
     *
     * @ORM\Column(name="configuration_key", type="string", length=255, nullable=false, unique=true)
     */
    private $key;

    /**
     * @var text $value
     *
     * @ORM\Column(name="configuration_value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var text $description
     *
     * @ORM\Column(name="configuration_description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var integer $productTypeId
     *
     * @ORM\Column(name="product_type_id", type="integer", nullable=false)
     */
    private $productTypeId;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="smallint", nullable=true)
     */
    private $sortOrder;

    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;

    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @var text $useFunction
     *
     * @ORM\Column(name="use_function", type="text", nullable=true)
     */
    private $useFunction;

    /**
     * @var text $setFunction
     *
     * @ORM\Column(name="set_function", type="text", nullable=true)
     */
    private $setFunction;

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param  string            $title
     * @return ProductTypeLayout
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set key
     *
     * @param  string            $key
     * @return ProductTypeLayout
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set value
     *
     * @param  string            $value
     * @return ProductTypeLayout
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set description
     *
     * @param  string            $description
     * @return ProductTypeLayout
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
     * Set productTypeId
     *
     * @param  integer           $productTypeId
     * @return ProductTypeLayout
     */
    public function setProductTypeId($productTypeId)
    {
        $this->productTypeId = $productTypeId;

        return $this;
    }

    /**
     * Get productTypeId
     *
     * @return integer
     */
    public function getProductTypeId()
    {
        return $this->productTypeId;
    }

    /**
     * Set sortOrder
     *
     * @param  integer           $sortOrder
     * @return ProductTypeLayout
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set lastModified
     *
     * @param  \DateTime         $lastModified
     * @return ProductTypeLayout
     */
    public function setLastModified(\DateTime $lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * Get lastModified
     *
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set dateAdded
     *
     * @param  \DateTime         $dateAdded
     * @return ProductTypeLayout
     */
    public function setDateAdded(\DateTime $dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Set useFunction
     *
     * @param  string            $useFunction
     * @return ProductTypeLayout
     */
    public function setUseFunction($useFunction)
    {
        $this->useFunction = $useFunction;

        return $this;
    }

    /**
     * Get useFunction
     *
     * @return string
     */
    public function getUseFunction()
    {
        return $this->useFunction;
    }

    /**
     * Set setFunction
     *
     * @param  string            $setFunction
     * @return ProductTypeLayout
     */
    public function setSetFunction($setFunction)
    {
        $this->setFunction = $setFunction;

        return $this;
    }

    /**
     * Get setFunction
     *
     * @return string
     */
    public function getSetFunction()
    {
        return $this->setFunction;
    }
}
