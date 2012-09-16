<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\StoreBundle\Entity\Templating;

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A single banner.
 *
 * @author DerManoMann
 * @ORM\Table(name="banners",
 *  indexes={
 *      @ORM\Index(name="idx_status_group_zen", columns={"status", "banners_group"}),
 *      @ORM\Index(name="idx_expires_date_zen", columns={"expires_date"}),
 *      @ORM\Index(name="idx_date_scheduled_zen", columns={"date_scheduled"}),
 *  })
 * @ORM\Entity
 */
class Banner extends ZMObject {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="banners_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var string $title
     *
     * @ORM\Column(name="banners_title", type="string", length=64, nullable=false)
     */
    private $title;
    /**
     * @var string $url
     *
     * @ORM\Column(name="banners_url", type="string", length=255, nullable=false)
     */
    private $url;
    /**
     * @var string $image
     *
     * @ORM\Column(name="banners_image", type="string", length=64, nullable=false)
     */
    private $image;
    /**
     * @var string $group
     *
     * @ORM\Column(name="banners_group", type="string", length=15, nullable=false)
     */
    private $group;
    /**
     * @var text $text
     *
     * @ORM\Column(name="banners_html_text", type="text", nullable=true)
     */
    private $text;
    /**
     * @var integer $expiryImpressions
     *
     * @ORM\Column(name="expires_impressions", type="integer", nullable=true)
     */
    private $expiryImpressions;
    /**
     * @var datetime $expiryDate
     *
     * @ORM\Column(name="expires_date", type="datetime", nullable=true)
     */
    private $expiryDate;
    /**
     * @var datetime $dateScheduled
     *
     * @ORM\Column(name="date_scheduled", type="datetime", nullable=true)
     */
    private $dateScheduled;
    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="date_status_change", type="datetime", nullable=true)
     */
    private $lastModified;
    /**
     * @var integer $active
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $active;
    /**
     * @var integer $isNewWin
     *
     * @ORM\Column(name="banners_open_new_windows", type="boolean", nullable=false)
     */
    private $isNewWin;
    /**
     * @var integer $isShowOnSsl
     *
     * @ORM\Column(name="banners_on_ssl", type="boolean", nullable=false)
     */
    private $isShowOnSsl;
    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="banners_sort_order", type="integer", nullable=false)
     */
    private $sortOrder;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->title = null;
        $this->image = null;
        $this->group = '';
        $this->text = null;
        $this->expiryImpressions = 0;
        $this->isNewWin = false;
        $this->isShowOnSsl = false;
        $this->url = null;
        $this->active = true;
        $this->setDateAdded('0001-01-01 00:00:00');
        $this->setLastModified(null);
        $this->sortOrder = 0;
    }


    /**
     * Get the banner id.
     *
     * @return int $id The banner id.
     */
    public function getId() { return $this->id; }

    /**
     * Get the banner title.
     *
     * @return string $title The banner title.
     */
    public function getTitle() { return $this->title; }

    /**
     * Get the banner image.
     *
     * @return string $image The banner image.
     */
    public function getImage() { return $this->image; }

    /**
     * Get the banner text.
     *
     * @return text $text The banner text.
     */
    public function getText() { return $this->text; }

    /**
     * Check if the banner click should open a new window.
     *
     * @return boolean $isNewWin <code>true</code> if the banner URL should be opened in a new window, <code>false</code> if not.
     */
    public function isNewWin() { return $this->isNewWin; }

    /**
     * Check if the banner is active.
     *
     * @return boolean $status <code>true</code> if the banner is active.
     */
    public function isActive() { return $this->active; }

    /**
     * Get the banner URL.
     *
     * @return string $url The banner URL.
     */
    public function getUrl() { return $this->url; }

    /**
     * Get bannersGroup
     *
     * @return string $bannersGroup
     */
    public function getGroup() { return $this->group; }

   /**
     * Get expiryImpressions
     *
     * @return integer $expiryImpressions
     */
    public function getExpiryImpressions() { return $this->expiryImpressions; }

    /**
     * Get expiryDate
     *
     * @return datetime $expiryDate
     */
    public function getExpiryDate() { return $this->expiryDate; }

    /**
     * Get dateScheduled
     *
     * @return datetime $dateScheduled
     */
    public function getDateScheduled() { return $this->dateScheduled; }

    /**
     * Get dateAdded
     *
     * @return datetime $dateAdded
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get dateStatusChange
     *
     * @return datetime $dateStatusChange
     */
    public function getDateStatusChange() { return $this->dateStatusChange; }

    /**
     * Get sortOrder
     *
     * @return integer $sortOrder
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Show banner on SSLed connection
     *
     * @return integer $isShowOnSsl
     */
    public function isShowOnSsl() { return $this->isShowOnSsl; }

    /**
     * Set the id.
     *
     * @param string $id The id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the banner title.
     *
     * @param string $title The banner title.
     */
    public function setTitle($title) { $this->title = $title; }

    /**
     * Set the banner image.
     *
     * @param string $image The banner image.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Set the banner text.
     *
     * @param string $text The banner text.
     */
    public function setText($text) { $this->text = $text; }

    /**
     * Set if the banner click should open a new window.
     *
     * @param boolean $newWin <code>true</code> if the banner URL should be opened in a new window, <code>false</code> if not.
     */
    public function setNewWin($newWin) { $this->isNewWin = $newWin; }

    /**
     * Set the banner URL.
     *
     * @param string $url The banner URL.
     */
    public function setUrl($url) { $this->url = $url; }

    /**
     * Set the banner status and log the date the status changed.
     *
     * @param boolean $status The banner status.
     */
    public function setActive($status) {
        $this->active = $status;
        $this->setDateStatusChange(new \DateTime());
    }

    /**
     * Set Group
     *
     * @param string $group
     */
    public function setGroup($group) { $this->group = $group; }

    /**
     * Set expiryImpressions
     *
     * @param integer $expiryImpressions
     */
    public function setExpiryImpressions($expiryImpressions) { $this->expiryImpressions = $expiryImpressions; }

    /**
     * Set expiryDate
     *
     * @param datetime $expiryDate
     */
    public function setExpiryDate($expiryDate) { $this->expiryDate = $expiryDate; }

    /**
     * Set dateScheduled
     *
     * @param datetime $dateScheduled
     */
    public function setDateScheduled($dateScheduled) { $this->dateScheduled = $dateScheduled; }

    /**
     * Set dateAdded
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set dateStatusChange
     *
     * @param datetime $dateStatusChange
     */
    public function setDateStatusChange($dateStatusChange) { $this->dateStatusChange = $dateStatusChange; }

    /**
     * Set showOnSsl
     *
     * @param integer $showOnSsl
     */
    public function setShowOnSsl($isShowOnSsl) { $this->isShowOnSsl = $isShowOnSsl; }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     */
    public function setBannersSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }

    /**
     * Set isNewWin
     *
     * @param integer $isNewWin
     */
    public function setIsNewWin($isNewWin) { $this->isNewWin = $isNewWin; }

    /**
     * Get isNewWin
     *
     * @return integer
     */
    public function getIsNewWin() { return $this->isNewWin; }

    /**
     * Get active
     *
     * @return integer
     */
    public function getActive() { return $this->active; }

    /**
     * Set lastModified
     *
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Get lastModified
     *
     * @return datetime
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Set isShowOnSsl
     *
     * @param integer $isShowOnSsl
     */
    public function setIsShowOnSsl($isShowOnSsl) { $this->isShowOnSsl = $isShowOnSsl; }

    /**
     * Get isShowOnSsl
     *
     * @return integer
     */
    public function getIsShowOnSsl() { return $this->isShowOnSsl; }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }
}
