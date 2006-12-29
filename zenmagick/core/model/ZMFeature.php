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
 * Single Feature.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMFeature {
    var $id_;
    var $type_;
    var $name_;
    var $description_;
    var $hidden_;
    var $values_;


    /**
     * Default c'tor.
     */
    function ZMFeature() {
        $this->id_ = 0;
		    $this->values_ = array();
    }

    // create new instance
    function __construct() {
        $this->ZMFeature();
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getDescription() { return $this->description_; }
    function isHidden() { return $this->hidden_; }
    function getValues() { return $this->values_; }
    function getType() { global $zm_features; return $zm_features->getFeatureTypeForId($this->type_); }

}

?>
