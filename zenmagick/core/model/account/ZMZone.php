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
 * A address zone.
 *
 * @author mano
 * @package org.zenmagick.model.account
 * @version $Id$
 */
class ZMZone extends ZMModel {
    private $id_;
    private $code_;
    private $name_;


    /**
     * Create new zone.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = 0;
        $this->code_ = null;
        $this->name_ = null;
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
     * Get the code.
     *
     * @return string The code.
     */
    public function getCode() { return $this->code_; }

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
     * Set the code.
     *
     * @param string code The code.
     */
    public function setCode($code) { $this->code_ = $code; }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) { $this->name_ = $name; }

}

?>
