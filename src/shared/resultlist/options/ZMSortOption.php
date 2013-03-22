<?php
/*
 * ZenMagick - Another PHP framework.
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
 * A single sort option.
 *
 * <p>The returned id will automatically reflect the current status, the id the sort
 * order.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist.options
 */
class ZMSortOption extends ZMObject
{
    private $name;
    private $id;
    private $active;
    private $decending;

    /**
     * Create a new sort option.
     *
     * @param string name The option name.
     * @param int id The option id.
     * @param boolean active Optional active flag if this option is currently active.
     * @param boolean decending Ascending/decending flag (default is ascending.
     */
    public function __construct($name, $id, $active=false, $decending=false)
    {
        parent::__construct();

        $this->name = $name;
        $this->id = $id;
        $this->active = $active;
        $this->decending = $decending;
    }

    /**
     * Get the option id.
     *
     * @return string The option id.
     */
    public function getId() { return $this->id; }

    /**
     * Get the reverse option id.
     *
     * @return string The reverse option id.
     */
    public function getReverseId() { return $this->id . ($this->decending ? '_a' : '_d'); }

    /**
     * Get the option name.
     *
     * @return string The option name.
     */
    public function getName() { return $this->name; }

    /**
     * Check if this option is active.
     *
     * @return boolean <code>true</code> if this option is active, <code>false</code>, if not.
     */
    public function isActive() { return $this->active; }

    /**
     * Check if the sorting is ascending or decending.
     *
     * @return boolean <code>true</code> if sorting is decending, <code>false</code> if sorting
     *  is ascending.
     */
    public function isDecending() { return $this->decending; }

}
