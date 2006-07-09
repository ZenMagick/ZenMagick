<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Protions Copyright (c) 2003 The zen-cart developers
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
 * A order total line.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMOrderTotal {
    var $name_;
    var $value_;
    var $type_;

    // create new instance
    function ZMOrderTotal($name, $value, $type) {
        $this->name_ = $name;
        $this->value_ = $value;
        $this->type_ = $type;
    }

    // create new instance
    function __construct($name, $value, $type) {
        $this->ZMOrderTotal($name, $value, $type);
    }

    function __destruct() {
    }


    // getter/setter
    function getName() { return $this->name_; }
    function getValue() { return $this->value_; }
    function getType() { return $this->type_; }

}

?>
