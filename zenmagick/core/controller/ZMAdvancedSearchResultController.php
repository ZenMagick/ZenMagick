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
 * Advanced search result controller.
 *
 * @author mano
 * @package net.radebatz.zenmagick.controller
 * @version $Id$
 */
class ZMAdvancedSearchResultController extends ZMController {

    // create new instance
    function ZMAdvancedSearchResultController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMAdvancedSearchResultController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
    global $zm_crumbtrail, $zm_products;
    global $listing_sql;

        // zc search sql
        $zm_crumbtrail->addCrumb(zm_title(false));

        $resultList = new ZMResultList($zm_products->getProductsForSQL($listing_sql));
        $this->exportGlobal("zm_resultList", $resultList);

        return true;
    }

}

?>
