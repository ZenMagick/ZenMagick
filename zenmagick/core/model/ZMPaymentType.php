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
 * A single payment type including all required information and settings.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMPaymentType {
    var $id_;
    var $name_;
    var $instructions_;
    var $error_;
    var $fields_;


    /**
     * Create a new payment type.
     *
     * @param int id The id.
     * @param string name The name.
     * @param string instructions Optional instructions.
     */
    function ZMPaymentType($id, $name, $instructions='') {
        $this->id_ = $id;
        $this->name_ = $name;
        $this->instructions_ = $instructions;
        $this->error_ = null;
        $this->fields_ = array();
    }

    // create new instance
    function __construct($id, $name, $instructions='') {
        $this->ZMPaymentType($id, $name, $instructions);
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getInstructions() { return $this->instructions_; }
    function getError() { return $this->error_; }
    function getFields() { return $this->fields_; }
    function addField($field) { array_push($this->fields_, $field); }

}

?>
