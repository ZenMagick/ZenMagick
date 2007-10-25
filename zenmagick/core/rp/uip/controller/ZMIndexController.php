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
 * Request controller for index.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMIndexController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMIndexController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMIndexController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request, $zm_categories, $zm_crumbtrail, $zm_products;

        // crumbtrail handling
        $zm_crumbtrail->addCategoryPath($zm_request->getCategoryPathArray());
        $zm_crumbtrail->addManufacturer($zm_request->getManufacturerId());
        $zm_crumbtrail->addProduct($zm_request->getProductId());

        // decide which index view to use and prepare index data
        $resultList = null;
        $max = zm_setting('maxProductResultList');
        $viewName = 'index';

        if (null != $zm_request->getCategoryPath()) {
            $resultList = new ZMResultList($zm_products->getProductsForCategoryId($zm_request->getCategoryId()), $max);
            $viewName = 'category_list';
        } else if (null != $zm_request->getManufacturerId()) {
            $resultList = new ZMResultList($zm_products->getProductsForManufacturerId($zm_request->getManufacturerId()), $max);
            $viewName = 'manufacturer';
        } else if (null != $zm_request->getParameter('compareId')) {
            $resultList = new ZMResultList($zm_products->getProductsForIds($zm_request->getParameter('compareId')), $max);
            $viewName = 'category_list';
        }
        if (null != $resultList) {
            $resultList->addFilter(new ZMManufacturerFilter());
            $resultList->addFilter(new ZMCategoryFilter());
            $resultList->addSorter(new ZMProductSorter());
            $resultList->refresh();
            $this->exportGlobal("zm_resultList", $resultList);
        }

        $category = $zm_categories->getCategoryForId($zm_request->getCategoryId());
        if ($viewName == "category_list" && ((!$resultList->hasResults() || (null != $category && $category->hasChildren())) && zm_setting('isUseCategoryPage'))) {
            $viewName = 'category';
        }

        $this->exportGlobal("zm_category", $category);

        if (null != $resultList && 1 == $resultList->getNumberOfResults() && zm_setting('isSkipSingleProductCategory')) {
            $product = array_pop($resultList->getResults());
            // TODO: do not use name directly!
            $zm_request->setParameterMap(array('products_id' => $product->getId()));
            $viewName = 'product_info';
        }

        return $this->findView($viewName);
    }

}

?>
