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
 * @ORM\Table(name="product_types",
 *   indexes={
 *     @ORM\Index(name="idx_type_master_type_zen", columns={"type_master_type"})
 * })
 * @ORM\Entity
 */
class ProductType
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="type_name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string $handler
     *
     * @ORM\Column(name="type_handler", type="string", length=255, nullable=false)
     */
    private $handler;

    /**
     * @var integer $masterType
     *
     * @ORM\Column(name="type_master_type", type="integer", nullable=false)
     */
    private $masterType;

    /**
     * @var string $addToCart
     *
     * @ORM\Column(name="allow_add_to_cart", type="string", length=1, nullable=false)
     */
    private $addToCart;

    /**
     * @var string $defaultImage
     *
     * @ORM\Column(name="default_image", type="string", length=255, nullable=false)
     */
    private $defaultImage;

    /**
     * @var \DateTime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @var \DateTime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    private $lastModified;


    public function __construct()
    {
        $this->dateAdded = new \DateTime;
        $this->lastModified = new \DateTime;
    }

   /**
     * Set id
     *
     * @param string $id
     * @return ProductType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set name
     *
     * @param string $name
     * @return ProductType
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
     * Set handler
     *
     * @param string $handler
     * @return ProductType
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Get handler
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set masterType
     *
     * @param integer $masterType
     * @return ProductType
     */
    public function setMasterType($masterType)
    {
        $this->masterType = $masterType;

        return $this;
    }

    /**
     * Get masterType
     *
     * @return integer
     */
    public function getMasterType()
    {
        return $this->masterType;
    }

    /**
     * Set addToCart
     *
     * @param string $addToCart
     * @return ProductType
     */
    public function setAddToCart($addToCart)
    {
        $this->addToCart = $addToCart;

        return $this;
    }

    /**
     * Get addToCart
     *
     * @return string
     */
    public function getAddToCart()
    {
        return $this->addToCart;
    }

    /**
     * Set defaultImage
     *
     * @param string $defaultImage
     * @return ProductType
     */
    public function setDefaultImage($defaultImage)
    {
        $this->defaultImage = $defaultImage;

        return $this;
    }

    /**
     * Get defaultImage
     *
     * @return string
     */
    public function getDefaultImage()
    {
        return $this->defaultImage;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     * @return ProductType
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
     * Set lastModified
     *
     * @param \DateTime $lastModified
     * @return ProductType
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
}
