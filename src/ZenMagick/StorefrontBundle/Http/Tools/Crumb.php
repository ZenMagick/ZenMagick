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
namespace ZenMagick\StorefrontBundle\Http\Tools;

use ZenMagick\Base\ZMObject;

/**
 * A crumbtrail crumb.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Crumb extends ZMObject
{
    private $name;
    private $url;

    /**
     * Create a new crumbtrail crumb.
     *
     * @param string name The name; default is <code>null</code>.
     * @param string url Optional url; default is <code>null</code>.
     */
    public function __construct($name=null, $url=null)
    {
        parent::__construct();
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * Get the name.
     *
     * @return string The crumb's name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the url (if any).
     *
     * @return string The crumb's url or <code>null</code>.
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Set the name.
     *
     * @param string name The crumb's name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set the url.
     *
     * @param string url The crumb's url.
     */
    public function setURL($url)
    {
        $this->url = $url;
    }

}
