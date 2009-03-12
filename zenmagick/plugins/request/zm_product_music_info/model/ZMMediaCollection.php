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
 * A collection of media items.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_music_info.model
 * @version $Id$
 */
class ZMMediaCollection extends ZMObject {
    var $name_;
    var $items_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->name_ = '';
        $this->items_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the media collection name.
     *
     * @return string The media collection name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the media items.
     *
     * @return array A list of <code>ZMMedia</code> objects.
     */
    function getItems() { return $this->items_; }

}

?>
