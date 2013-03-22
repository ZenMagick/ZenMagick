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

use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;

/**
 * An artist.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Artist extends ZMObject
{
    private $artistId;
    private $name;
    private $genre;
    private $image;
    private $url;
    private $recordCompany;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        $this->artistId = 0;
        $this->name = '';
        $this->genre = '';
        $this->image = null;
        $this->url = null;
        $this->recordCompany = null;
    }

    /**
     * Get the artist id.
     *
     * @return int The artist id.
     */
    public function getArtistId() { return $this->artistId; }

    /**
     * Get the artist name.
     *
     * @return string The artist name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the genre.
     *
     * @return string The genre.
     */
    public function getGenre() { return $this->genre; }

    /**
     * Check if an image is available.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    public function hasImage() { return !empty($this->image); }

    /**
     * Get the artist image.
     *
     * @return string The artist image.
     */
    public function getImage() { return $this->image; }

    /**
     * Get the image info.
     *
     * @return ZMImageInfo The image info.
     */
    public function getImageInfo()
    {
        $imageInfo = Beans::getBean('ZMImageInfo');
        $imageInfo->setAltText($this->name);
        $imageInfo->setDefaultImage($this->image);

        return $imageInfo;
    }

    /**
     * Check if a URL is available.
     *
     * @return boolean <code>true</code> if a URL is available, <code>false</code> if not.
     */
    public function hasUrl() { return !empty($this->url); }

    /**
     * Get the artist URL.
     *
     * @return string The artist URL.
     */
    public function getUrl() { return $this->url; }

    /**
     * Get the record company.
     *
     * @return ZMRecordCompany The record company.
     */
    public function getRecordCompany() { return $this->recordCompany; }

    /**
     * Set the artist id.
     *
     * @param int id The artist id.
     */
    public function setArtistId($id) { $this->artistId = $id; }

    /**
     * Set the artist name.
     *
     * @param string name The artist name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the genre.
     *
     * @param string genre The genre.
     */
    public function setGenre($genre) { $this->genre = $genre; }

    /**
     * Set the artist image.
     *
     * @param string image The artist image.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Set the artist URL.
     *
     * @param string url The artist URL.
     */
    public function setUrl($url) { $this->url = $url; }

    /**
     * Set the record company.
     *
     * @param ZMRecordCompany recordCompany The record company.
     */
    public function setRecordCompany($recordCompany) { $this->recordCompany = $recordCompany; }

}
