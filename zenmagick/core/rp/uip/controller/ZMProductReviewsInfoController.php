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
 * Request controller for product review pages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMProductReviewsInfoController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMProductReviewsInfoController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMProductReviewsInfoController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_products, $zm_reviews;

        // crumbtrail handling
        $zm_crumbtrail->addCategoryPath($zm_request->getCategoryPathArray());
        $zm_crumbtrail->addManufacturer($zm_request->getManufacturerId());
        $zm_crumbtrail->addProduct($zm_request->getProductId());
        $zm_crumbtrail->addCrumb("Reviews");

        $product = $zm_products->getProductForId($zm_request->getProductId());
        $this->exportGlobal("zm_product", $product);

        $review = $zm_reviews->getReviewForId($zm_request->getReviewId());
        $this->exportGlobal("zm_review", $review);

        //$zm_reviews->updateViewCount($zm_request->getReviewId());

        return $this->findView('product_reviews_info');
    }

}

?>
