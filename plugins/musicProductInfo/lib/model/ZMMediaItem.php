<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\ZMObject;

/**
 * A single media item.
 *
 * @author mano
 * @package org.zenmagick.plugins.musicProductInfo.model
 */
class ZMMediaItem extends ZMObject {
    private $mediaItemId_;
    private $mediaId_;
    private $mediaTypeId_;
    private $filename_;
    private $dateAdded_;
    private $mediaType_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->mediaItemId_ = 0;
        $this->mediaId_ = 0;
        $this->mediaTypeId_ = 0;
        $this->filename_ = null;
        $this->dateAdded_ = null;
        $this->mediaType_ = null;
    }


    /**
     * Get the media item id.
     *
     * @return int The media item id.
     */
    public function getMediaItemId() { return $this->mediaItemId_; }

    /**
     * Get the media id.
     *
     * @return int The media id.
     */
    public function getMediaId() { return $this->mediaId_; }

    /**
     * Get the media filename.
     *
     * @return string The media filename.
     */
    public function getFilename() { return $this->filename_; }

    /**
     * Get the added date.
     *
     * @return string The date the media was added.
     */
    public function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the media type id.
     *
     * @return int The media type id.
     */
    public function getMediaTypeId() { return $this->mediaTypeId_; }

    /**
     * Get the media type.
     *
     * @return ZMMediaType The media type.
     */
    public function getMediaType() { return $this->mediaType_; }

    /**
     * Set the media item id.
     *
     * @param int id The media item id.
     */
    public function setMediaItemId($id) { $this->mediaItemId_ = $id; }

    /**
     * Set the media id.
     *
     * @param int mediaId The media id.
     */
    public function setMediaId($mediaId) { $this->mediaId_ = $mediaId; }

    /**
     * Set the media filename.
     *
     * @param string filename The media filename.
     */
    public function setFilename($filename) { $this->filename_ = $filename; }

    /**
     * Set the added date.
     *
     * @param string date The date the media was added.
     */
    public function setDateAdded($date) { $this->dateAdded_ = $date; }

    /**
     * Set the media type id.
     *
     * @param int id The media type id.
     */
    public function setMediaTypeId($id) { $this->mediaTypeId_ = $id; }

    /**
     * Set the media type.
     *
     * @param ZMMediaType mediaType The media type.
     */
    public function setMediaType($mediaType) { $this->mediaType_ = $mediaType; }

}
