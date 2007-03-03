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
 * Filter products by a single category.
 *
 * @author mano
 * @package net.radebatz.zenmagick.resultlist.filter
 * @version $Id$
 */
class ZMCategoryFilter extends ZMResultListFilter {
    var $productIds_;


    /**
     * Default c'tor.
     */
    function ZMCategoryFilter() {
    global $zm_request;

        parent::__construct('cfilter', zm_l10n_get('Category'));

        $this->productIds_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCategoryFilter();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // lazy load all included productIds
    function _getProductIds() {
    global $zm_products;

        if (null === $this->productIds_) {
            $this->productIds_ = $zm_products->getProductIdsForCategoryId($this->filterValue_);
        }
        return $this->productIds_;
    }


    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return bool <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    function exclude($obj) {
        $productIds = $this->_getProductIds();
        return !array_key_exists($obj->getId(), $productIds);
    }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    function getOptions() {
        $options = array();
        foreach ($this->list_->getAllResults() as $result) {
            $category = $result->getDefaultCategory();
            if (null != $category) {
                $option =& $this->create("FilterOption", $category->getName(), $category->getId(), $category->getId() == $this->filterValues_[0]);
                $options[$option->getId()] = $option;
            }
        }

        return $options;
    }

}

?>
