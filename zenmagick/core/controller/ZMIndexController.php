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
 * Request controller for index.
 *
 * @author mano
 * @package net.radebatz.zenmagick.controller
 * @version $Id$
 */
class ZMIndexController extends ZMController {

    // create new instance
    function ZMIndexController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMIndexController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
    global $zm_request, $zm_categories, $zm_crumbtrail, $zm_products;

        // crumbtrail handling
        $zm_crumbtrail->addCategoryPath($zm_request->getCategoryPathArray());
        $zm_crumbtrail->addManufacturer($zm_request->getManufacturerId());
        $zm_crumbtrail->addProduct($zm_request->getProductId());

        // decide which index view to use and prepare index data
        $resultList = null;
        $viewName = "index";
        $max = zm_setting('maxProductResultList');
        if (null != $zm_request->getCategoryPath()) {
            $resultList = new ZMResultList($zm_products->getProductsForCategoryId($zm_request->getCategoryId()), $max);
            $viewName = "category_list";
        } else if (null != $zm_request->getManufacturerId()) {
            $resultList = new ZMResultList($zm_products->getProductsForManufacturerId($zm_request->getManufacturerId()), $max);
            $viewName = "manufacturer";
        } else if (null != $zm_request->getRequestParameter('compareId')) {
            $resultList = new ZMResultList($zm_products->getProductsForIds($zm_request->getRequestParameter('compareId')), $max);
            $viewName = "category_list";
        }

        $category = $zm_categories->getCategoryForId($zm_request->getCategoryId());
        if ($viewName == "category_list" && ((!$resultList->hasResults() || $category->hasChildren()) && zm_setting('isUseCategoryPage'))) {
            $viewName = "category";
        }

        $this->exportGlobal("zm_category", $category);
        $this->exportGlobal("zm_resultList", $resultList);
        $this->setResponseView(new ZMView($viewName, $viewName));

        return true;
    }

}

?>
