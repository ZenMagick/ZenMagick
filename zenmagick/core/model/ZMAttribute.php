<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 radebatz.net
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
 * A single attribute.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMAttribute {
    var $id_;
    var $name_;
    var $type_;
    var $sortOrder_;
    var $comment_;
    var $values_;


    /**
     * Default c'tor.
     */
    function ZMAttribute($id, $name, $type) {
        $this->id_ = $id;
        $this->name_ = $name;
        $this->type_ = $type;
        $this->values_ = array();
    }

    // create new instance
    function __construct($id, $name, $type) {
        $this->ZMAttribute($id, $name, $type);
    }

    function __destruct() {
    }


    // getter/setter
    function getId() { return $this->id_; }
    function getName() { return $this->name_; }
    function getType() { return $this->type_; }
    function getSortOrder() { return $this->sortOrder_; }
    function getComment() { return $this->comment_; }
    function getValues() { return $this->values_; }

}

?>
