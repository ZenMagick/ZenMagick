<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * A price group.
 *
 * @author mano
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMPriceGroup extends ZMModel {
    var $id_;
    var $name_;
    var $discount_;
    var $dateAdded_;
    var $lastModified_;


    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();

        $this->id_ = 0;
        $this->name_ = 0;
        $this->discount_ = 0;
        $this->dateAdded_ = '';
        $this->lastModified_ = '';
    }

    /**
     * Default c'tor.
     */
    function ZMPriceGroup() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the group id.
     *
     * @return int The group id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the name.
     *
     * @return string The group name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the discount.
     *
     * @return float The discount.
     */
    function getDiscount() { return $this->discount_; }

    /**
     * Get the date the group was added.
     *
     * @return string The added date.
     */
    function getDateAdded() { return $this->dateAdded_; }

    /**
     * Get the last modified date.
     *
     * @return string The last modified date.
     */
    function getLastModified() { return $this->lastModified_; }

    /**
     * Set the group id.
     *
     * @param int id The group id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Set the name.
     *
     * @param string name The group name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Set the discount.
     *
     * @param float discount The discount.
     */
    function setDiscount($discount) { $this->discount_ = $discount; }

    /**
     * Set the date the group was added.
     *
     * @param string dateAdded The added date.
     */
    function setDateAdded($dateAdded) { $this->dateAdded_ = $dateAdded; }

    /**
     * Set the last modified date.
     *
     * @param string lastModified The last modified date.
     */
    function setLastModified($lastModified) { $this->lastModified_ = $lastModified; }

}

?>
