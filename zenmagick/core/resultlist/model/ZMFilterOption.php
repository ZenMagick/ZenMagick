<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
?>
<?php


/**
 * A single filter option.
 *
 * @author mano
 * @package net.radebatz.zenmagick.resultlist.model
 * @version $Id$
 */
class ZMFilterOption extends ZMModel {
    var $name_;
    var $id_;
    var $active_;


    /**
     * Create a new filter option.
     *
     * @param string name The option name.
     * @param int id The option id.
     * @param bool active Optional active flag if this option is currently active.
     */
    function ZMFilterOption($name, $id, $active=false) {
        parent::__construct();

        $this->name_ = $name;
        $this->id_ = $id;
        $this->active_ = $active;
    }

    /**
     * Create a new filter option.
     *
     * @param string name The option name.
     * @param int id The option id.
     * @param bool active Optional active flag if this option is currently active.
     */
    function __construct($name, $id, $active=false) {
        $this->ZMFilterOption($name, $id, $active);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the option id.
     *
     * @return int The option id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the option name.
     *
     * @return string The option name.
     */
    function getName() { return $this->name_; }

    /**
     * Check if this option is active.
     *
     * @return bool <code>true</code> if this option is active, <code>false</code>, if not.
     */
    function isActive() { return $this->active_; }

}

?>
