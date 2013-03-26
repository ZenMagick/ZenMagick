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
 * A single media type.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MediaType extends ZMObject
{
    private $id;
    private $name;
    private $extension;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->id = 0;
        $this->name = null;
        $this->extension = null;
    }

    /**
     * Get the media type id.
     *
     * @return int The media type id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the media type name.
     *
     * @return string The media type name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the media type file extension.
     *
     * @return string The media type file extension.
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set the media type id.
     *
     * @param int id The media type id.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the media type name.
     *
     * @param string name The media type name.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the media type file extension.
     *
     * @param string extension The media type file extension.
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

}
