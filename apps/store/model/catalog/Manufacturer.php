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
namespace zenmagick\apps\store\model\catalog;

use zenmagick\base\Beans;
use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Manufacturer.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @ORM\Table(name="manufacturers")
 * @ORM\Entity
 */
class Manufacturer extends ZMObject {
    /**
     * @var integer $manufacturerId
     *
     * @ORM\Column(name="manufacturers_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $manufacturerId;
    /**
     * @var string $name
     *
     * @ORM\Column(name="manufacturers_name", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $image
     *
     * @ORM\Column(name="manufacturers_image", type="string", length=64, nullable=true)
     */
    private $image;
    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;
    private $languageId;
    private $url;
    private $clickCount;
    private $lastClick;


    /**
     * Create new instance
     */
    public function __construct() {
        parent::__construct();
        $this->setId(0);
        $this->name = null;
        $this->image = null;
        $this->languageId = 0;
        $this->url = null;
        $this->clickCount = 0;
        $this->lastClick = null;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }


    /**
     * Get the manufacturer id.
     *
     * @return integer $manufacturerId The manufacturer id.
     */
    public function getId() { return $this->manufacturerId; }

    //@todo deprecated doctrine backwards compatibility
    public function getManufacturerId() { return $this->getId(); }

    /**
     * Get the manufacturer name.
     *
     * @return string $name The manufacturer name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the manufacturer image.
     *
     * @return string $image The manufacturer image.
     */
    public function getImage() { return $this->image; }

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
     * Check if a manufacturer image exists.
     *
     * @return boolean <code>true</code> if an image exists, <code>false</code> if not.
     */
    public function hasImage() { return !empty($this->image); }

    /**
     * Get the manufacturer image info.
     *
     * @return ZMImageInfo The image info.
     */
    public function getImageInfo() {
        $imageInfo = Beans::getBean('ZMImageInfo');
        $imageInfo->setAltText($this->name);
        $imageInfo->setDefaultImage($this->image);
        return $imageInfo;
    }

    /**
     * Get the manufacturer url.
     *
     * @return string The manufacturer url.
     */
    public function getUrl() { return $this->url; }

    /**
     * Set the manufacturer id.
     *
     * @param int id The manufacturer id.
     */
    public function setId($id) { $this->manufacturerId = $id; }

    // @todo deprecated doctrine backwards compatbility
    public function setManufacturerId($id) { $this->setId($id); }

    /**
     * Set the manufacturer name.
     *
     * @param string $name The manufacturer name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the manufacturer image.
     *
     * @param string $image The manufacturer image.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Set dateAdded
     *
     * @author  DerManoMann
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set lastModified
     *
     * @author  DerManoMann
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Set the manufacturer url.
     *
     * @param string url The manufacturer url.
     */
    public function setUrl($url) { $this->url = $url; }

    /**
     * Get the language id.
     *
     * @return int The language id.
     */
    public function getLanguageId() { return $this->languageId; }

    /**
     * Set the language id.
     *
     * @param int languageId The language id.
     */
    public function setLanguageId($languageId) { $this->languageId = $languageId; }

    /**
     * Get the click counter.
     *
     * @return int The click count for this manufactuer and language.
     */
    public function getClickCount() { return $this->clickCount; }

    /**
     * Set the click counter.
     *
     * @param int clickCount The click count for this manufactuer and language.
     */
    public function setClickCount($clickCount) { $this->clickCount = $clickCount; }

    /**
     * Get the date of the last click.
     *
     * @return date The last click date.
     */
    public function getLastClick() { return $this->lastClick; }

    /**
     * Set the date of the last click.
     *
     * @param date lastClick The last click date.
     */
    public function setLastClick($lastClick) { $this->lastClick = $lastClick; }

}
