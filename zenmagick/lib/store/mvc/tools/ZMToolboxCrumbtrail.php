<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 * @version $Id$
 */
class ZMToolboxCrumbtrail extends ZMToolboxTool {
    private $crumbs_;


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
     * {@inheritDoc}
     */
    public function setToolbox($toolbox) {
        parent::setToolbox($toolbox);
        $this->reset();
    }

    /**
     * Reset.
     */
    public function reset() {
        $this->crumbs_ = array();
        // always add home
        $this->addCrumb("Home", $this->getToolbox()->net->url(FILENAME_DEFAULT, '', false, false));
    }

    /**
     * Clear all crumbs.
     */
    public function clear() {
        $this->crumbs_ = array();
    }

    /**
     * Get the last crumbs name.
     *
     * @return string The name of the last crumbtrail element.
     */
    public function getLastCrumb() {
        return end($this->crumbs_);
    }

    /**
     * Get the crumb for the given index.
     *
     * @param int index The index of the crumb to access.
     * @return ZMCrumb The corresponding crumbtrail element.
     */
    public function getCrumb($index) {
        return $this->crumbs_[$index];
    }

    /**
     * Get a list of all crumbs.
     *
     * @return array List of <code>ZMCrumb</code> instances.
     */
    public function getCrumbs() {
        return $this->crumbs_;
    }

    /**
     * Add a single crumb.
     *
     * @param string name The crumbtrail element name.
     * @param string url Optional crumbtrail element URL.
     */
    public function addCrumb($name, $url = null) {
        array_push($this->crumbs_, ZMLoader::make("Crumb", $name, $url));
    }

    /**
     * Add the given category path to the crumbtrail.
     *
     * @param array path The category path to add as a list of category ids.
     */
    public function addCategoryPath($path) {
        if (null == $path)
            return;

        // categories
        foreach ($path as $catId) {
            $category = ZMCategories::instance()->getCategoryForId($catId);
            if (null == $category) {
                return;
            }
            $this->addCrumb($category->getName(),$this->getToolbox()->net->url('category', $category->getPath(), false, false));
        }
    }

    /**
     * Add manufacturer to the crumbtrail.
     *
     * @param int manufacturerId The manufacturer's id.
     */
    public function addManufacturer($manufacturerId) {
        if (null == $manufacturerId)
            return;

        $manufacturer = ZMManufacturers::instance()->getManufacturerForId($manufacturerId);
        if (null != $manufacturer) {
            $this->addCrumb($manufacturer->getName(), $this->getToolbox()->net->url('category', 'manufacturers_id=' . $manufacturerId, false, false));
        }
    }

    /**
     * Add product to the crumbtrail.
     *
     * @param int productId The product id of the product to add.
     */
    public function addProduct($productId) {
        if (null == $productId)
            return;

        $product = ZMProducts::instance()->getProductForId($productId);
        if (null != $product) {
            $this->addCrumb($product->getName(), $this->getToolbox()->net->product($productId, null, false));
        }
    }

}

?>
