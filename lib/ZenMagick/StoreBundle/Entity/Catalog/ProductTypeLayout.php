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
 *      @ORM\Index(name="idx_key_value_zen", columns={"configuration_key", "configuration_value"}),
 *      @ORM\Index(name="idx_type_id_sort_order_zen", columns={"product_type_id", "sort_order"}),
 * })
 * @ORM\Entity
 */
class ProductTypeLayout extends ZMObject {
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
     * @ORM\Column(name="configuration_title", type="text", nullable=false)
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
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
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

     /**
     * Get id
     *
     * @return integer $id
     */
    public function getId() { return $this->id; }

    /**
     * Get title
     *
     * @return text $title
     */
    public function getTitle() { return $this->title; }

    /**
     * Get key
     *
     * @return string $key
     */
    public function getKey() { return $this->key; }

    /**
     * Get value
     *
     * @return text $value
     */
    public function getValue() { return $this->value; }

    /**
     * Get description
     *
     * @return text $description
     */
    public function getDescription() { return $this->description; }

    /**
     * Get productTypeId
     *
     * @return integer $productTypeId
     */
    public function getProductTypeId() { return $this->productTypeId; }

    /**
     * Get sortOrder
     *
     * @return integer $sortOrder
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Get lastModified
     *
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Get dateAdded
     *
     * @return datetime $dateAdded
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get useFunction
     *
     * @return text $useFunction
     */
    public function getUseFunction() { return $this->useFunction; }

    /**
     * Get setFunction
     *
     * @return text $setFunction
     */
    public function getSetFunction() { return $this->setFunction; }

    /**
     * Set title
     *
     * @param text $title
     */
    public function setTitle($title) { $this->title = $title; }

    /**
     * Set key
     *
     * @param string $key
     */
    public function setKey($key) { $this->key = $key; }

    /**
     * Set value
     *
     * @param text $value
     */
    public function setValue($value) { $this->value = $value; }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set productTypeId
     *
     * @param integer $productTypeId
     */
    public function setProductTypeId($productTypeId) { $this->productTypeId = $productTypeId; }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }

    /**
     * Set lastModified
     *
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Set dateAdded
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set useFunction
     *
     * @param text $useFunction
     */
    public function setUseFunction($useFunction) { $this->useFunction = $useFunction; }

    /**
     * Set setFunction
     *
     * @param text $setFunction
     */
    public function setSetFunction($setFunction) { $this->setFunction = $setFunction; }
}
