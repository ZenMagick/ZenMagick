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

use zenmagick\base\ZMObject;

/**
 * A single filter option.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.resultlist.options
 */
class ZMFilterOption extends ZMObject {
    private $name_;
    private $id_;
    private $active_;


    /**
     * Create a new filter option.
     *
     * @param string name The option name; default is <code>null</code>.
     * @param int id The option id; default is <code>null</code>.
     * @param boolean active Optional active flag if this option is currently active; default is <code>false</code>.
     */
    function __construct($name=null, $id=null, $active=false) {
        parent::__construct();
        $this->name_ = $name;
        $this->id_ = $id;
        $this->active_ = $active;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the option id.
     *
     * @return int The option id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the option name.
     *
     * @return string The option name.
     */
    public function getName() { return $this->name_; }

    /**
     * Check if this option is active.
     *
     * @return boolean <code>true</code> if this option is active, <code>false</code>, if not.
     */
    public function isActive() { return $this->active_; }

    /**
     * Set the option id.
     *
     * @param int id The option id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Set the option name.
     *
     * @param string name The option name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the active flag.
     *
     * @param boolean value The new value.
     */
    public function setActive($value) { $this->active_ = $value; }

}
