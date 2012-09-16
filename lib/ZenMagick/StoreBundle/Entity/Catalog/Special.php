<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\StoreBundle\Entity\Catalog;

use ZenMagick\Base\ZMObject;
use Doctrine\ORM\Mapping AS ORM;

/**
 * A Special.
 *
 * @ORM\Table(name="specials",
 *  indexes={
 *      @ORM\Index(name="idx_status_zen", columns={"status"}),
 *      @ORM\Index(name="idx_products_id_zen", columns={"products_id"}),
 *      @ORM\Index(name="idx_date_avail_zen", columns={"specials_date_available"}),
 *      @ORM\Index(name="idx_expires_date_zen", columns={"expires_date"}),
 *  })
 * @ORM\Entity
 */
class Special extends ZMObject {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="specials_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var integer $productId
     *
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     */
    private $productId;
    /**
     * @var decimal $specialPrice
     *
     * @ORM\Column(name="specials_new_products_price", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $specialPrice;
    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="specials_date_added", type="datetime", nullable=true)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="specials_last_modified", type="datetime", nullable=true)
     */
    private $lastModified;
    /**
     * @var date $expiryDate
     *
     * @ORM\Column(name="expires_date", type="date", nullable=false)
     */
    private $expiryDate;
    /**
     * @var datetime $statusChangeDate
     *
     * @ORM\Column(name="date_status_change", type="datetime", nullable=true)
     */
    private $statusChangeDate;
    /**
     * @var boolean $status
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;
    /**
     * @var date $availableDate
     *
     * @ORM\Column(name="specials_date_available", type="date", nullable=false)
     */
    private $availableDate;

    public function __construct() {
        $this->status = 1;
        $this->expiryDate = '0001-01-01';
        $this->availableDate = '0001-01-01';
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId() { return $this->id; }

    /**
     * Get productId
     *
     * @return integer $productId
     */
    public function getProductId() { return $this->productId; }

    /**
     * Get specialPrice
     *
     * @return decimal $specialPrice
     */
    public function getSpecialPrice() { return $this->specialPrice; }

    /**
     * Get dateAdded
     *
     * @return datetime $dateAdded
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get lastModified
     *
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Get expiryDate
     *
     * @return date $expiryDate
     */
    public function getExpiryDate() { return $this->expiryDate; }

    /**
     * Get statusChangeDate
     *
     * @return datetime $statusChangeDate
     */
    public function getStatusChangeDate() { return $this->statusChangeDate; }

    /**
     * Get status
     *
     * @return integer $status
     */
    public function getStatus() { return $this->status; }

    /**
     * Get availableDate
     *
     * @return date $availableDate
     */
    public function getAvailableDate() { return $this->availableDate; }

    /**
     * Set productId
     *
     * @param integer $productId
     */
    public function setProductId($productId) { $this->productId = $productId; }

    /**
     * Set specialPrice
     *
     * @param decimal $specialPrice
     */
    public function setSpecialPrice($specialPrice) { $this->specialPrice = $specialPrice; }

    /**
     * Set dateAdded
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set lastModified
     *
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Set expiryDate
     *
     * @param date $expiryDate
     */
    public function setExpiryDate($expiryDate) { $this->expiryDate = $expiryDate; }

    /**
     * Set statusChangeDate
     *
     * @param datetime $statusChangeDate
     */
    public function setStatusChangeDate($statusChangeDate) { $this->statusChangeDate = $statusChangeDate; }

    /**
     * Set status and log the date the status changed.
     *
     * @param integer $status
     */
    public function setStatus($status) {
        $this->status = $status;
        $this->setStatusChangeDate(new \DateTime());
    }

    /**
     * Set availableDate
     *
     * @param date $availableDate
     */
    public function setAvailableDate($availableDate) { $this->availableDate = $availableDate; }
}
