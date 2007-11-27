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
 * Crumbtrail.
 *
 * @author mano
 * @package org.zenmagick.misc
 * @version $Id$
 */
class ZMCrumbtrail extends ZMObject {
    var $crumbs_;


    /**
     * Default c'tor.
     */
    function ZMCrumbtrail() {
        parent::__construct();

        $this->reset();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCrumbtrail();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Reset.
     */
    function reset() {
        $this->crumbs_ = array();
        // always add home
        $this->addCrumb("Home", zm_href(FILENAME_DEFAULT, '', false));
    }

    /**
     * Clear all crumbs.
     */
    function clear() {
        $this->crumbs_ = array();
    }

    /**
     * Get the last crumbs name.
     *
     * @return string The name of the last crumbtrail element.
     */
    function getLastCrumb() {
        return end($this->crumbs_);
    }

    /**
     * Get the crumb for the given index.
     *
     * @param int index The index of the crumb to access.
     * @return ZMCrumb The corresponding crumbtrail element.
     */
    function getCrumb($index) {
        return $this->crumbs_[$index];
    }

    /**
     * Get a list of all crumbs.
     *
     * @return array List of <code>ZMCrumb</code> instances.
     */
    function getCrumbs() {
        return $this->crumbs_;
    }

    /**
     * Add a single crumb.
     *
     * @param string name The crumbtrail element name.
     * @param string url Optional crumbtrail element URL.
     */
    function addCrumb($name, $url = null) {
        array_push($this->crumbs_, $this->create("Crumb", $name, $url));
    }

    /**
     * Add the given category path to the crumbtrail.
     *
     * @param array path The category path to add as a list of <code>ZMCategory</code> instances.
     */
    function addCategoryPath($path) {
    global $zm_categories;

        if (null == $path)
            return;

        // categories
        foreach ($path as $catId) {
            $category =& $zm_categories->getCategoryForId($catId);
            $this->addCrumb($category->getName(), zm_href(FILENAME_DEFAULT, $category->getPath(), false));
        }
    }

    /**
     * Add manufacturer to the crumbtrail.
     *
     * @param int manufacturerId The manufacturer's id.
     */
    function addManufacturer($manufacturerId) {
    global $zm_manufacturers;
        if (null == $manufacturerId)
            return;

        $manufacturer = $zm_manufacturers->getManufacturerForId($manufacturerId);
        if (null != $manufacturer) {
            $this->addCrumb($manufacturer->getName(), zm_href(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturerId, false));
        }
    }

    /**
     * Add product to the crumbtrail.
     *
     * @param int productId The product id of the product to add.
     */
    function addProduct($productId) {
    global $zm_request, $zm_products;

        if (null == $productId)
            return;

        $product = $zm_products->getProductForId($productId);
        if (null != $product) {
            $this->addCrumb($product->getName(), zm_product_href($productId, false));
        }
    }

}

?>
