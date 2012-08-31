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
namespace ZenMagick\plugins\musicProductInfo\model;

use ZenMagick\Base\ZMObject;

/**
 * A record company.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RecordCompany extends ZMObject {
    private $recordCompanyId_;
    private $name_;
    private $image_;
    private $url_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->recordCompanyId_ = 0;
        $this->name_ = '';
        $this->image_ = null;
        $this->url_ = null;
    }


    /**
     * Get the record company id.
     *
     * @return int The record company id.
     */
    public function getRecordCompanyId() { return $this->recordCompanyId_; }

    /**
     * Get the record company name.
     *
     * @return string The name.
     */
    public function getName() { return $this->name_; }

    /**
     * Check if an image is available.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    public function hasImage() { return !empty($this->image_); }

    /**
     * Get the record company image.
     *
     * @return string The record company image.
     */
    public function getImage() { return $this->image_; }

    /**
     * Checks if a URL exists for this company.
     *
     * @return boolean <code>true</code> if a URL exists, <code>false</code> if not.
     */
    public function hasUrl() { return !empty($this->url_); }

    /**
     * Get the record company ULR.
     *
     * @return string The URL.
     */
    public function getUrl() { return $this->url_; }

    /**
     * Set the record company id.
     *
     * @param int id The record company id.
     */
    public function setRecordCompanyId($id) { $this->recordCompanyId_ = $id; }

    /**
     * Set the record company name.
     *
     * @param string name The name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the record company image.
     *
     * @param string image The record company image.
     */
    public function setImage($image) { $this->image_ = $image; }

    /**
     * Set the record company ULR.
     *
     * @param string url The URL.
     */
    public function setUrl($url) { $this->url_ = $url; }

}
