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
 * A record company.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_music_info.model
 * @version $Id: ZMRecordCompany.php 292 2007-08-13 03:18:35Z DerManoMann $
 */
class ZMRecordCompany extends ZMModel {
    var $id_;
    var $name_;
    var $url_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->id_ = 0;
        $this->name_ = '';
        $this->url_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the record company id.
     *
     * @return int The record company id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the record company name.
     *
     * @return string The name.
     */
    function getName() { return $this->name_; }

    /**
     * Checks if a URL exists for this company.
     *
     * @return boolean <code>true</code> if a URL exists, <code>false</code> if not.
     */
    function hasUrl() { return !empty($this->url_); }

    /**
     * Get the record company ULR.
     *
     * @return string The URL.
     */
    function getUrl() { return $this->url_; }

}

?>
