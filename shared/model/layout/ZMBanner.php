<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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


/**
 * A single banner.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.layout
 * @Table(name="banners")
 * @Entity
 */
class ZMBanner extends ZMObject {
    /**
     * @var integer $id
     *
     * @Column(name="banners_id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var string $title
     *
     * @Column(name="banners_title", type="string", length=64, nullable=false)
     */
    private $title;
    /**
     * @var string $image
     *
     * @Column(name="banners_image", type="string", length=64, nullable=false)
     */
    private $image;
    /**
     * @var text $text
     *
     * @Column(name="banners_html_text", type="text", nullable=true)
     */
    private $text;
    /**
     * @var integer $isNewWin
     *
     * @Column(name="banners_open_new_windows", type="integer", nullable=false)
     */
    private $isNewWin;
    /**
     * @var string $url
     *
     * @Column(name="banners_url", type="string", length=255, nullable=false)
     */
    private $url;
    /**
     * @var integer $active
     *
     * @Column(name="status", type="integer", nullable=false)
     */
    private $active;
    /**
     * @var string $group
     *
     * @Column(name="banners_group", type="string", length=15, nullable=false)
     */
    private $group;
    /**
     * @var integer $expiresImpressions
     *
     * @Column(name="expires_impressions", type="integer", nullable=true)
     */
    private $expiresImpressions;
    /**
     * @var datetime $expiresDate
     *
     * @Column(name="expires_date", type="datetime", nullable=true)
     */
    private $expiresDate;
    /**
     * @var datetime $dateScheduled
     *
     * @Column(name="date_scheduled", type="datetime", nullable=true)
     */
    private $dateScheduled;
    /**
     * @var datetime $dateAdded
     *
     * @Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @Column(name="date_status_change", type="datetime", nullable=true)
     */
    private $lastModified;
    /**
     * @var integer $isShowOnSsl
     *
     * @Column(name="banners_on_ssl", type="integer", nullable=false)
     */
    private $isShowOnSsl;

    /**
     * @var integer $sortOrder
     *
     * @Column(name="banners_sort_order", type="integer", nullable=false)
     */
    private $sortOrder;
    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->title = null;
        $this->image = null;
        $this->text = null;
        $this->isNewWin = false;
        $this->url = null;
        $this->active = true;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
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
     * Get expiresImpressions
     *
     * @return integer $expiresImpressions
     */
    public function getExpiresImpressions() { return $this->expiresImpressions; }

    /**
     * Get expiresDate
     *
     * @return datetime $expiresDate
     */
    public function getExpiresDate() { return $this->expiresDate; }

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
     * Set the banner status.
     *
     * @param boolean $status The banner status.
     */
    public function setActive($status) { $this->active = $status; }

    /**
     * Set Group
     *
     * @param string $group
     */
    public function setGroup($group) { $this->group = $group; }


    /**
     * Set expiresImpressions
     *
     * @author  DerManoMann
     * @param integer $expiresImpressions
     */
    public function setExpiresImpressions($expiresImpressions) { $this->expiresImpressions = $expiresImpressions; }

    /**
     * Set expiresDate
     *
     * @param datetime $expiresDate
     */
    public function setExpiresDate($expiresDate) { $this->expiresDate = $expiresDate; }

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
}
