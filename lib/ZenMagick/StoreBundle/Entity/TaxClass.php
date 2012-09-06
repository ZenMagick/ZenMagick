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
namespace ZenMagick\StoreBundle\Entity;

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Info for a single tax class.
 *
 * @author DerManoMann
 * @ORM\Table(name="tax_class")
 * @ORM\Entity
 */
class TaxClass extends ZMObject {
    /**
     * @var integer $taxClassId
     *
     * @ORM\Column(name="tax_class_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @todo change to private $id
     */
    private $taxClassId;
    /**
     * @var string $title
     *
     * @ORM\Column(name="tax_class_title", type="string", length=32, nullable=false)
     */
    private $title;
    /**
     * @var string $description
     *
     * @ORM\Column(name="tax_class_description", type="string", length=255, nullable=false)
     */
    private $description;
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
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->taxClassId_ = 0;
        $this->title = '';
        $this->description = '';
        $this->lastModified = null;
        $this->dateAdded = null;
    }


    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setTaxClassId($id) { $this->taxClassId = $id; }

    /**
     * Set the title.
     *
     * @param string $title The title.
     */
    public function setTitle($title) { $this->title = $title; }

    /**
     * Set the description.
     *
     * @param string $description The description.
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set the date the class was added.
     *
     * @param datetime $dateAdded The added date.
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set the last modified date.
     *
     * @param datetime $lastModified The last modified date.
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Get the id.
     *
     * @return integer $taxClassId The id.
     */
    public function getId() { return $this->taxClassId; }

    /**
     * Get the id.
     *
     * @deprecated
     * @return integer $taxClassId The id.
     */
    public function getTaxClassId() { return $this->getId(); }

    /**
     * Get the title.
     *
     * @return string $title The title.
     */
    public function getTitle() { return $this->title; }

    /**
     * Get the description.
     *
     * @return string $description The description.
     */
    public function getDescription() { return $this->description; }

    /**
     * Get the date the class was added.
     *
     * @return datetime $dateAdded The added date.
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get the last modified date.
     *
     * @return datetime $lastModified The last modified date.
     */
    public function getLastModified() { return $this->lastModified; }
}