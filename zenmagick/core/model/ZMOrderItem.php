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
 * A single order item
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMOrderItem extends ZMModel {
    var $id_;
    var $qty_;
    var $name_;
    var $model_;
    var $taxRate_;
    var $calculatedPrice_;
    var $attributes_;


    /**
     * Default c'tor.
     */
    function ZMOrderItem() {
        parent::__construct();

        $this->attributes_ = array();
    }

    // create new instance
    function __construct() {
        $this->ZMOrderItem();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getQty() { return $this->qty_; }
    function getName() { return $this->name_; }
    function getModel() { return $this->model_; }
    function getTaxRate() { return $this->taxRate_; }
    function getCalculatedPrice() { return $this->calculatedPrice_; }
    function hasAttributes() { return 0 < count($this->attributes_); }
    function getAttributes() { return $this->attributes_; }

}

?>
