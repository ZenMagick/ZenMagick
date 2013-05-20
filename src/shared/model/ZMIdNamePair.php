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

use ZenMagick\Base\ZMObject;

/**
 * Simple id/name container.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMIdNamePair extends ZMObject
{
    public $id;
    public $name;

    /**
     * Create new id - name pair.
     *
     * @param int id The id.
     * @param string name The name.
     */
    public function __construct($id = null, $name = null)
    {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Get the id.
     *
     * @return string The id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Id
     *
     * @param int id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
