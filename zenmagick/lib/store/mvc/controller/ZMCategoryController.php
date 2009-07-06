<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Request controller for categories.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMCategoryController.php 2348 2009-06-29 03:04:18Z dermanomann $
 */
class ZMCategoryController extends ZMController {

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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process($request) { 
        ZMCrumbtrail::instance()->addCategoryPath($request->getCategoryPathArray());
        ZMCrumbtrail::instance()->addManufacturer($request->getManufacturerId());
        ZMCrumbtrail::instance()->addProduct($request->getProductId());

        return parent::process($request);
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet($request) {
        $viewName = 'error';
        $method = null;
        $args = null;
        $data = array();

        // decide what to do
        if (null != $request->getCategoryPath()) {
            $method = "getProductsForCategoryId";
            $args = array($request->getCategoryId());
            $viewName = 'category_list';
        } else if (null != $request->getManufacturerId()) {
            $method = "getProductsForManufacturerId";
            $args = array($request->getManufacturerId());
            $viewName = 'manufacturer';
        }

        $resultList = null;
        if (null !== $method) {
            $resultSource = ZMLoader::make("ObjectResultSource", 'Product', ZMProducts::instance(), $method, $args);
            $resultList = ZMLoader::make("ResultList");
            $resultList->setResultSource($resultSource);
            foreach (explode(',', ZMSettings::get('resultListProductFilter')) as $filter) {
                $resultList->addFilter(ZMLoader::make($filter));
            }
            foreach (explode(',', ZMSettings::get('resultListProductSorter')) as $sorter) {
                $resultList->addSorter(ZMLoader::make($sorter));
            }
            $resultList->setPageNumber($request->getPageIndex());
            $data['zm_resultList'] = $resultList;
        }

        $category = ZMCategories::instance()->getCategoryForId($request->getCategoryId());

        if ($viewName == "category_list" 
            && ((null == $resultList || !$resultList->hasResults() || (null != $category && $category->hasChildren())) 
                && ZMSettings::get('isUseCategoryPage'))) {
            $viewName = 'category';
        }

        if (null != $category) {
            $data['zm_category'] = $category;
        }

        if (null != $resultList && 1 == $resultList->getNumberOfResults() && ZMSettings::get('isSkipSingleProductCategory')) {
            $results = $resultList->getResults();
            $product = array_pop($results);
            $request->setParameter('products_id', $product->getId());
            $viewName = 'product_info';
        }

        return $this->findView($viewName, $data);
    }

}

?>
