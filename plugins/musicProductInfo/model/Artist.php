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
namespace zenmagick\plugins\musicProductInfo\model;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * An artist.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Artist extends ZMObject {
    private $artistId_;
    private $name_;
    private $genre_;
    private $image_;
    private $url_;
    private $recordCompany_;


    /**
     * Create new instance.
     */
    function __construct() {
        $this->artistId_ = 0;
        $this->name_ = '';
        $this->genre_ = '';
        $this->image_ = null;
        $this->url_ = null;
        $this->recordCompany_ = null;
    }


    /**
     * Get the artist id.
     *
     * @return int The artist id.
     */
    public function getArtistId() { return $this->artistId_; }

    /**
     * Get the artist name.
     *
     * @return string The artist name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the genre.
     *
     * @return string The genre.
     */
    public function getGenre() { return $this->genre_; }

    /**
     * Check if an image is available.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    public function hasImage() { return !empty($this->image_); }

    /**
     * Get the artist image.
     *
     * @return string The artist image.
     */
    public function getImage() { return $this->image_; }

    /**
     * Get the image info.
     *
     * @return ZMImageInfo The image info.
     */
    public function getImageInfo() {
        $imageInfo = Runtime::getContainer()->get("ZMImageInfo");
        $imageInfo->setAltText($this->name_);
        $imageInfo->setDefaultImage($this->image_);
        return $imageInfo;
    }

    /**
     * Check if a URL is available.
     *
     * @return boolean <code>true</code> if a URL is available, <code>false</code> if not.
     */
    public function hasUrl() { return !empty($this->url_); }

    /**
     * Get the artist URL.
     *
     * @return string The artist URL.
     */
    public function getUrl() { return $this->url_; }

    /**
     * Get the record company.
     *
     * @return ZMRecordCompany The record company.
     */
    public function getRecordCompany() { return $this->recordCompany_; }

    /**
     * Set the artist id.
     *
     * @param int id The artist id.
     */
    public function setArtistId($id) { $this->artistId_ = $id; }

    /**
     * Set the artist name.
     *
     * @param string name The artist name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the genre.
     *
     * @param string genre The genre.
     */
    public function setGenre($genre) { $this->genre_ = $genre; }

    /**
     * Set the artist image.
     *
     * @param string image The artist image.
     */
    public function setImage($image) { $this->image_ = $image; }

    /**
     * Set the artist URL.
     *
     * @param string url The artist URL.
     */
    public function setUrl($url) { $this->url_ = $url; }

    /**
     * Set the record company.
     *
     * @param ZMRecordCompany recordCompany The record company.
     */
    public function setRecordCompany($recordCompany) { $this->recordCompany_ = $recordCompany; }

}
