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
 * A search.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMSearch {

    // create new instance
    function ZMSearch() {
    }

    // create new instance
    function __construct() {
        $this->ZMSearch();
    }

    function __destruct() {
    }


    function _get($key, $default='') {global $zm_request; return $zm_request->getRequestParameter($key, $default); }

    // getter/setter
    function getKeyword($default='') { return $this->_get('keyword', $default); }
    function getIncludeDescription($default=true) { return $this->_get('search_in_description', $default); }
    function getCategory($default=0) { return $this->_get('categories_id', $default); }
    function getIncludeSubcategories($default=true) { return $this->_get('inc_subcat', $default); }
    function getManufacturer($default=0) { return $this->_get('manufacturers_id', $default); }
    function getDateFrom($default='') { return $this->_get('dfrom', $default); }
    function getDateTo($default='') { return $this->_get('dto', $default); }
    function getPriceFrom($default='') { return $this->_get('pfrom', $default); }
    function getPriceTo($default='') { return $this->_get('pto', $default); }

}

?>
