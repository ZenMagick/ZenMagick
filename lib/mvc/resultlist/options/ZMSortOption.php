<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php

use zenmagick\base\ZMObject;

/**
 * A single sort option.
 *
 * <p>The returned id will automatically reflect the current status, the id the sort
 * order.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.resultlist.options
 */
class ZMSortOption extends ZMObject {
    private $name_;
    private $id_;
    private $active_;
    private $decending_;


    /**
     * Create a new sort option.
     *
     * @param string name The option name.
     * @param int id The option id.
     * @param boolean active Optional active flag if this option is currently active.
     * @param boolean decending Ascending/decending flag (default is ascending.
     */
    function __construct($name, $id, $active=false, $decending=false) {
        parent::__construct();

        $this->name_ = $name;
        $this->id_ = $id;
        $this->active_ = $active;
        $this->decending_ = $decending;
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
     * @return string The option id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the reverse option id.
     *
     * @return string The reverse option id.
     */
    public function getReverseId() { return $this->id_ . ($this->decending_ ? '_a' : '_d'); }

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
     * Check if the sorting is ascending or decending.
     *
     * @return boolean <code>true</code> if sorting is decending, <code>false</code> if sorting
     *  is ascending.
     */
    public function isDecending() { return $this->decending_; }

}
