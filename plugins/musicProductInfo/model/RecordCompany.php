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
class RecordCompany extends ZMObject
{
    private $recordCompanyId;
    private $name;
    private $image;
    private $url;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->recordCompanyId = 0;
        $this->name = '';
        $this->image = null;
        $this->url = null;
    }

    /**
     * Get the record company id.
     *
     * @return int The record company id.
     */
    public function getRecordCompanyId()
    {
        return $this->recordCompanyId;
    }

    /**
     * Get the record company name.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check if an image is available.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    public function hasImage()
    {
        return !empty($this->image);
    }

    /**
     * Get the record company image.
     *
     * @return string The record company image.
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Checks if a URL exists for this company.
     *
     * @return boolean <code>true</code> if a URL exists, <code>false</code> if not.
     */
    public function hasUrl()
    {
        return !empty($this->url);
    }

    /**
     * Get the record company ULR.
     *
     * @return string The URL.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the record company id.
     *
     * @param int id The record company id.
     */
    public function setRecordCompanyId($id)
    {
        $this->recordCompanyId = $id;

        return $this;
    }

    /**
     * Set the record company name.
     *
     * @param string name The name.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the record company image.
     *
     * @param string image The record company image.
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Set the record company ULR.
     *
     * @param string url The URL.
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

}
