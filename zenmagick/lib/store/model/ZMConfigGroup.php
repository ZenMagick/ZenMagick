<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Configuration group.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model
 * @version $Id: ZMConfigGroup.php 2054 2009-03-12 03:41:22Z dermanomann $
 */
class ZMConfigGroup extends ZMObject {
    private $id_;
    private $name_;


    /**
     * Create new config group.
     */
    function __construct() {
        parent::__construct();
		    $this->id_ = 0;
		    $this->name_ = '';
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return string The id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() { return $this->name_; }

    /**
     * Set the id.
     *
     * @param string id The id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) {return $this->name_ = $name; }

}

?>
