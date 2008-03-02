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
 * Request controller for index.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_split_page_result_list
 * @version $Id$
 */
class IndexController extends ZMIndexController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function IndexController() {
        $this->__construct();
    }

    /**
     * Destruct instance.
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
    global $zm_request, $zm_categories, $zm_crumbtrail;

        if (null == $zm_request->getCategoryPath() && null == $zm_request->getManufacturerId()) {
            // default
            return parent::processGet();
        }

        //**** START  prepare zen-cart environment
        global $db;
        $current_category_id = $zm_request->getCategoryId();

        //***** include zen-cart code
        require_once(DIR_WS_INCLUDES . 'index_filters/default_filter.php');

        //***** translate sort order
        $sortId = $zm_request->getSortId();
        $desc = false;
        if (zm_ends_with($sortId, '_d')) {
            $desc = true;
            $sortId = str_replace('_d', '', $sortId);
        } else {
            $sortId = str_replace('_a', '', $sortId);
        }

        $listing_sql .= ' order by ';
        switch ($sortId) {
        case 'model':
            $listing_sql .= "p.products_model " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        case 'name':
            $listing_sql .= "pd.products_name " . ($desc ? 'desc' : '');
            break;
        case 'manufacturer':
            $listing_sql .= "m.manufacturers_name " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        case 'weight':
            $listing_sql .= "p.products_weight " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        case 'price':
            $listing_sql .= "p.products_price_sorter " . ($desc ? 'desc' : '') . ", pd.products_name";
            break;
        default:
            $listing_sql .= "p.products_sort_order, pd.products_name";
            break;
        }

        //zm_resolve_zc_class('split_page_results');
        $listing_split = new splitPageResults($listing_sql, zm_setting('maxProductResultList'), 'p.products_id', 'page');

        /**** make proper products */
        $products = ZMProducts::instance()->getProductsForSQL($listing_split->sql_query);

        $resultList = $this->create("SplitPageResultList", $products, $listing_split, zm_setting('maxProductResultList'));
        //$resultList = $this->create("ResultList", $products, zm_setting('maxProductResultList'));
        $resultList->addSorter(new ZMProductSorter());
        $this->exportGlobal("zm_resultList", $resultList);
        $viewName = 'category_list';

        $category = $zm_categories->getCategoryForId($zm_request->getCategoryId());
        if ($viewName == "category_list" && ((!$resultList->hasResults() || (null != $category && $category->hasChildren())) && zm_setting('isUseCategoryPage'))) {
            $viewName = 'category';
        }

        $this->exportGlobal("zm_category", $category);

        if (null != $resultList && 1 == $resultList->getNumberOfResults() && zm_setting('isSkipSingleProductCategory')) {
            $product = array_pop($resultList->getResults());
            // TODO: do not use name directly?
            $zm_request->setParameterMap(array('products_id' => $product->getId()));
            $viewName = 'product_info';
        }

        return $this->findView($viewName);
    }

}

?>
