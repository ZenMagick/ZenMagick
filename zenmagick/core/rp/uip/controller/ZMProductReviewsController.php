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
 * Request controller for product reviews pages.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMProductReviewsController extends ZMController {

    /**
     * Create new instance.
     */
    function ZMProductReviewsController() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMProductReviewsController();
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
    global $zm_request;

        // crumbtrail handling
        ZMCrumbtrail::instance()->addCategoryPath($zm_request->getCategoryPathArray());
        ZMCrumbtrail::instance()->addManufacturer($zm_request->getManufacturerId());
        ZMCrumbtrail::instance()->addProduct($zm_request->getProductId());
        ZMCrumbtrail::instance()->addCrumb("Reviews");

        $product = ZMProducts::instance()->getProductForId($zm_request->getProductId());
        $this->exportGlobal("zm_product", $product);

        $resultList = $this->create("ResultList", ZMReviews::instance()->getReviewsForProductId($product->getId()));
        $this->exportGlobal("zm_resultList", $resultList);

        return $this->findView();
    }

}

?>
