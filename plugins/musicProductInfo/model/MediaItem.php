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
 * A single media item.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MediaItem extends ZMObject
{
    private $mediaItemId;
    private $mediaId;
    private $mediaTypeId;
    private $filename;
    private $dateAdded;
    private $mediaType;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mediaItemId = 0;
        $this->mediaId = 0;
        $this->mediaTypeId = 0;
        $this->filename = null;
        $this->dateAdded = null;
        $this->mediaType = null;
    }

    /**
     * Get the media item id.
     *
     * @return int The media item id.
     */
    public function getMediaItemId() { return $this->mediaItemId; }

    /**
     * Get the media id.
     *
     * @return int The media id.
     */
    public function getMediaId() { return $this->mediaId; }

    /**
     * Get the media filename.
     *
     * @return string The media filename.
     */
    public function getFilename() { return $this->filename; }

    /**
     * Get the added date.
     *
     * @return string The date the media was added.
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get the media type id.
     *
     * @return int The media type id.
     */
    public function getMediaTypeId() { return $this->mediaTypeId; }

    /**
     * Get the media type.
     *
     * @return MediaType The media type.
     */
    public function getMediaType() { return $this->mediaType; }

    /**
     * Set the media item id.
     *
     * @param int id The media item id.
     */
    public function setMediaItemId($id) { $this->mediaItemId = $id; }

    /**
     * Set the media id.
     *
     * @param int mediaId The media id.
     */
    public function setMediaId($mediaId) { $this->mediaId = $mediaId; }

    /**
     * Set the media filename.
     *
     * @param string filename The media filename.
     */
    public function setFilename($filename) { $this->filename = $filename; }

    /**
     * Set the added date.
     *
     * @param string date The date the media was added.
     */
    public function setDateAdded($date) { $this->dateAdded = $date; }

    /**
     * Set the media type id.
     *
     * @param int id The media type id.
     */
    public function setMediaTypeId($id) { $this->mediaTypeId = $id; }

    /**
     * Set the media type.
     *
     * @param MediaType mediaType The media type.
     */
    public function setMediaType($mediaType) { $this->mediaType = $mediaType; }

}
