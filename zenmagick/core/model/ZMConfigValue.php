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
 * Configuration value.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMConfigValue extends ZMModel {
    var $id_;
    var $name_;
    var $key_;
    var $value_;
    var $description_;
    var $useFunction_;
    var $setFunction_;


    /**
     * Create new config value.
     */
    function ZMConfigValue() {
        parent::__construct();

		    $this->id_ = 0;
    }

    /**
     * Create new config value.
     */
    function __construct() {
        $this->ZMConfigValue();
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
    function getId() { return $this->id_; }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the key.
     *
     * @return string The key.
     */
    function getKey() { return $this->key_; }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    function getValue() { return $this->value_; }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    function getDescription() { return $this->description_; }

    /**
     * Get the use function.
     *
     * @return string The use function.
     * @deprecated
     */
    function getUseFunction() { return $this->useFunction_; }

    /**
     * Get the set function.
     *
     * @return string The set function.
     * @deprecated
     */
    function getSetFunction() { return $this->setFunction_; }

    /**
     * Check if a set function is set or not.
     *
     * @return boolean <code>true</code> if a set function is configured, <code>false<code> if not.
     */
    function hasSetFunction() {
        return !empty($this->setFunction_);
    }

    /**
     * Set the key.
     *
     * @param string key The key.
     */
    function setKey($key) { $this->key_ = $key; }

}

?>
