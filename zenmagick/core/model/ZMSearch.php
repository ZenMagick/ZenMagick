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
 * A search.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMSearch extends ZMModel {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Conveniece access method to the request.
     */
    function _get($key, $default='') { return ZMRequest::getParameter($key, $default); }

    /**
     * Get the keyword.
     *
     * @param string default A default value.
     * @return string The keyword.
     */
    function getKeyword($default='') { return $this->_get('keyword', $default); }

    /**
     * Get the include description flag.
     *
     * @param string default A default value.
     * @return boolean <code>true</code> if descriptions should be searched too.
     */
    function getIncludeDescription($default=true) { return $this->_get('search_in_description', $default); }

    /**
     * Get the category.
     *
     * @param string default A default value.
     * @return string The category.
     */
    function getCategory($default=0) { return $this->_get('categories_id', $default); }

    /**
     * Get the include subcategories flag.
     *
     * @param string default A default value.
     * @return boolean <code>true</code> if subcategories should be searched too.
     */
    function getIncludeSubcategories($default=true) { return $this->_get('inc_subcat', $default); }

    /**
     * Get the manufacturer.
     *
     * @param string default A default value.
     * @return string The manufacturer.
     */
    function getManufacturer($default='') { return $this->_get('manufacturers_id', $default); }

    /**
     * Get the from date.
     *
     * @param string default A default value.
     * @return string The from date.
     */
    function getDateFrom($default='') { return $this->_get('dfrom', $default); }

    /**
     * Get the to date.
     *
     * @param string default A default value.
     * @return string The to date.
     */
    function getDateTo($default='') { return $this->_get('dto', $default); }

    /**
     * Get the price from.
     *
     * @param string default A default value.
     * @return string The price from.
     */
    function getPriceFrom($default='') { return $this->_get('pfrom', $default); }

    /**
     * Get the price to.
     *
     * @param string default A default value.
     * @return string The price to.
     */
    function getPriceTo($default='') { return $this->_get('pto', $default); }

}

?>
