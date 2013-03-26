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

namespace ZenMagick\StoreBundle\Entity\Catalog;

use ZenMagick\Base\ZMObject;

/**
 * Meta tag details.
 *
 * @author DerManoMann
 */
class MetaTagDetails extends ZMObject
{
    private $title;
    private $keywords;
    private $description;

    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the keywords.
     *
     * @return string The keywords.
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the title.
     *
     * @param string title The title.
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the keywords.
     *
     * @param string keywords The keywords.
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

}
