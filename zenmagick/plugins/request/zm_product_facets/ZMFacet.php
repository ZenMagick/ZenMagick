<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * A single facet.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_facets
 * @version $Id$
 */
class ZMFacet extends ZMObject {
    private $id_;
    private $name_;
    private $map_;


    /**
     * Create new instance.
     *
     * @param string id The id.
     * @param string name The name.
     * @param array name The map.
     */
    function __construct($id=null, $name=null, $map=array()) {
        parent::__construct();
        $this->id_ = $id;
        $this->name_ = $name;
        $this->map_ = $map;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the id.
     *
     * @param string id The facet id.
     */
    public function setId($id) {
        $this->id_ = $id;
    }

    /**
     * Set the name.
     *
     * @param string name The facet name.
     */
    public function setName($name) {
        $this->name_ = $name;
    }

    /**
     * Get the id.
     *
     * @return string The facet id.
     */
    public function getId() {
        return $this->id_;
    }

    /**
     * Get the name.
     *
     * @return string The facet name.
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * Get the facet map.
     *
     * @return array The facet map.
     */
    public function getMap() {
    }

    /**
     * Get the hits of facet.
     *
     * @return array List of hit objects.
     */
    public function getHits() {
        $map = $this->getMap();
        return $map['hits'];
    }

}

?>
