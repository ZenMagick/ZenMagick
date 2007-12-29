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
 * Advanced search result controller.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAdvancedSearchResultController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMAdvancedSearchResultController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAdvancedSearchResultController();
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
    global $zm_crumbtrail, $zm_products;
    global $listing_sql;

        // zc search sql
        $zm_crumbtrail->addCrumb("Advanced Search", zm_href(FILENAME_ADVANCED_SEARCH, null, false));
        $zm_crumbtrail->addCrumb("Results");

        $resultList = $this->create("ResultList", $zm_products->getProductsForSQL($listing_sql));
        if (null != $resultList) {
            $sorter =& $this->create("ProductSorter");
            $sorter->setDefaultSortId(zm_setting('defaultProductSortOrder'));
            $resultList->addSorter($sorter);
            $resultList->refresh();
        }
        $this->exportGlobal("zm_resultList", $resultList);

        return $this->findView();
    }

}

?>
