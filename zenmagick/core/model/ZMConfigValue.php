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
 * @author DerManoMann
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMConfigValue extends ZMModel {
    private $id_;
    private $name_;
    private $key_;
    private $value_;
    private $description_;
    private $useFunction_;
    private $setFunction_;


    /**
     * Create new config value.
     */
    function __construct() {
        parent::__construct();
		    $this->id_ = 0;
		    $this->name_ = '';
		    $this->description_ = '';
		    $this->key_ = null;
		    $this->value_ = null;
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
     * Get the key.
     *
     * @return string The key.
     */
    public function getKey() { return $this->key_; }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function getValue() { return $this->value_; }

    /**
     * Get the description.
     *
     * @return string The description.
     */
    public function getDescription() { return $this->description_; }

    /**
     * Get the use function.
     *
     * @return string The use function.
     * @deprecated
     */
    public function getUseFunction() { return $this->useFunction_; }

    /**
     * Get the set function.
     *
     * @return string The set function.
     * @deprecated
     */
    public function getSetFunction() { return $this->setFunction_; }

    /**
     * Check if a set function is set or not.
     *
     * @return boolean <code>true</code> if a set function is configured, <code>false<code> if not.
     */
    public function hasSetFunction() {
        return !empty($this->setFunction_);
    }

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
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the key.
     *
     * @param string key The key.
     */
    public function setKey($key) { $this->key_ = $key; }

    /**
     * Set the value.
     *
     * @param mixed value The value.
     */
    public function setValue($value) { $this->value_ = $value; }

    /**
     * Set the description.
     *
     * @param string description The description.
     */
    public function setDescription($description ) { $this->description_ = $description ; }

    /**
     * Set the use function.
     *
     * @param string function The use function.
     * @deprecated
     */
    public function setUseFunction($function) { $this->useFunction_ = $function; }

    /**
     * Set the set function.
     *
     * @param string function The use function.
     * @deprecated
     */
    public function setSetFunction($function) { $this->setFunction_ = $function; }

}

?>
