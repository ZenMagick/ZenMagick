<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * @package org.zenmagick.model.catalog
 * @version $Id$
 */
class ZMAttribute extends ZMModel {
    var $id_;
    var $name_;
    var $type_;
    var $sortOrder_;
    var $comment_;
    var $values_;


    /**
     * Create new attribute.
     *
     * @param int id The id.
     * @param string name The name.
     * @param string type The type.
     */
    function ZMAttribute($id, $name, $type) {
        parent::__construct();

        $this->id_ = $id;
        $this->name_ = $name;
        $this->type_ = $type;
        $this->values_ = array();
    }

    /**
     * Create new attribute.
     *
     * @param int id The id.
     * @param string name The name.
     * @param string type The type.
     */
    function __construct($id, $name, $type) {
        $this->ZMAttribute($id, $name, $type);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the attribute id.
     *
     * @return int The attribute id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the attribute name.
     *
     * @return string The attribute name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the attribute type.
     *
     * @return string The attribute type.
     */
    function getType() { return $this->type_; }

    /**
     * Get the attribute sort order.
     *
     * @return int The attribute sort order.
     */
    function getSortOrder() { return $this->sortOrder_; }

    /**
     * Get the attribute comment.
     *
     * @return string The attribute comment.
     */
    function getComment() { return $this->comment_; }

    /**
     * Get the attribute values.
     *
     * @return array A list of <code>ZMAttributeValue</code> objects.
     */
    function getValues() { return $this->values_; }

}

?>
