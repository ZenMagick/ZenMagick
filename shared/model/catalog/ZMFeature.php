<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php
use zenmagick\base\ZMObject;
use Doctrine\ORM\Mapping AS ORM;

/**
 * A featured product.
 *
 * @package zenmagick.store.shared.model.catalog
 * @ORM\Table(name="featured")
 * @ORM\Entity
 */
class ZMFeature extends ZMObject {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="featured_id", type="integer", nullable=false)
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
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="featured_date_added", type="datetime", nullable=true)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="featured_last_modified", type="datetime", nullable=true)
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
     * @Column(name="date_status_change", type="datetime", nullable=true)
     */
    private $statusChangeDate;
    /**
     * @var integer $status
     *
     * @Column(name="status", type="boolean", nullable=false)
     */
    private $status;
    /**
     * @var date $availableDate
     *
     * @Column(name="featured_date_available", type="date", nullable=false)
     */
    private $availableDate;

    /**
     * Get the featured id.
     *
     * @return integer $id
     */
    public function getId() { return $this->id; }

    /**
     * Get the featured producdt id 
     *
     * @return integer $productId
     */
    public function getProductId() { return $this->productId; }

    /**
     * Get the date the featured product was added.
     *
     * @return datetime $dateAdded
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get the date the featured product was last modified.
     *
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Get the date the featured product expires.
     *
     * @return date $expiryDate
     */
    public function getExpiryDate() { return $this->expiryDate; }

    /**
     * Get dateStatusChange
     *
     * @return datetime $dateStatusChange
     */
    public function getDateStatusChange() { return $this->dateStatusChange; }

    /**
     * Get the date the featured product is available.
     *
     * @return date $availableDate
     */
    public function getAvailableDate() { return $this->availableDate; }

    /**
     * Get the featured product status.
     *
     * @return integer $status
     */
    public function getStatus() { return $this->status; }

    /**
     * Set the featured id.
     *
     * @param string $id The id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the featured product id 
     *
     * @param integer $productId
     */
    public function setProductId($productId) { $this->productId = $productId; }

    /**
     * Set the date the featured product was added.
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set the date the featured product was last modified.
     *
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Set the date the featured product will expire.
     *
     * @param date $expiryDate
     */
    public function setExpiryDate($expiryDate) { $this->expiryDate = $expiryDate; }

    /**
     * Set the the date the featured product status has changed.
     *
     * @param datetime $statusChangeDate
     */
    public function setStatusChangeDate($statusChangeDate) { $this->statusChangeDate = $statusChangeDate; }

    /**
     * Set the featured product status.
     *
     * @param integer $status
     */
    public function setStatus($status) {
        $this->status = $status;
        $this->setStatusChangeDate(new \DateTime());
    }

    /**
     * Set the date the featured product will be available.
     *
     * @param date $availableDate
     */
    public function setAvailableDate($availableDate) { $this->availableDate = $availableDate; }
}
