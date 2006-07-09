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
 * Crumbtrail.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMCrumbtrail {
    // db access
    var $db_;
    var $crumbs_;


    // create new instance
    function ZMCrumbtrail() {
    global $zm_runtime;
        $this->db_ = $zm_runtime->getDB();
        $this->crumbs_ = array();

        // always add home
        $this->addCrumb(HEADER_TITLE_CATALOG, zm_href(FILENAME_DEFAULT, '', false));
    }

    // create new instance
    function __construct() {
        $this->ZMCrumbtrail();
    }

    function __destruct() {
    }


    // get the last crumbs name
    function getLastCrumb() {
        return $this->crumbs_[count($this->crumbs_)-1];
    }

    // return crumb for the given index
    function getCrumb($index) {
        return $this->crumbs_[$index];
    }

    // return all crumbs
    function getCrumbs() {
        return $this->crumbs_;
    }

    // add a single element
    function addCrumb($name, $url = null) {
        array_push($this->crumbs_, new ZMCrumb($name, $url));
    }


    // add a complete path; i.e. an array containing category ids
    function addCategoryPath($path) {
    global $zm_categories;
        if (null == $path)
            return;

        // categories
        foreach ($path as $catId) {
            $category = $zm_categories->getCategoryForId($catId);          
            $this->addCrumb($category->getName(), zm_href(FILENAME_DEFAULT, $category->getPath(), false));
        }
    }

    // add manufacturer (by id)
    function addManufacturer($manufacturerId) {
    global $zm_manufacturers;
        if (null == $manufacturerId)
            return;

        $manufacturer = $zm_manufacturers->getManufacturerForId($manufacturerId);
        if (null != $manufacturer) {
            $this->addCrumb($manufacturer->getName(), zm_href(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturerId, false));
        }
    }

    // add product (by id)
    function addProduct($productId) {
    global $zm_request;
        if (null == $productId)
            return;

        //TODO: move to Product.php
        $sql = "select products_name from " . TABLE_PRODUCTS_DESCRIPTION . "
                where products_id = '" . (int)$productId . "' and language_id = '" . (int)$zm_request->getLanguageId() . "'";
        $results = $this->db_->Execute($sql);
        if (0 < $results->RecordCount()) {
            $this->addCrumb($results->fields['products_name'], zm_href(zm_get_info_page($productId), '&products_id=' . $productId, false));
        }
    }

}

?>
