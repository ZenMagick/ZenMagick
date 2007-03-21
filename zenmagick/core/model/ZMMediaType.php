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
 * A single media type.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMMediaType extends ZMModel {
    var $id_;
    var $name_;
    var $extension_;


    /**
     * Default c'tor.
     */
    function ZMMediaType() {
        parent::__construct();

        $this->id_ = 0;
        $this->name_ = null;
        $this->extension_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMMediaType();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the media type id.
     *
     * @return int The media type id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the media type name.
     *
     * @return string The media type name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the media type file extension.
     *
     * @return string The media type file extension.
     */
    function getExtension() { return $this->extension_; }

}

?>
