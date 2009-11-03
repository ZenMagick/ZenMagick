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
 * Request controller for product reviews pages.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMProductReviewsController.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMProductReviewsController extends ZMController {

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
    public function processGet($request) {
        // crumbtrail handling
        $request->getCrumbtrail()->addCategoryPath($request->getCategoryPathArray());
        $request->getCrumbtrail()->addManufacturer($request->getManufacturerId());
        $request->getCrumbtrail()->addProduct($request->getProductId());
        $request->getCrumbtrail()->addCrumb("Reviews");

        $product = ZMProducts::instance()->getProductForId($request->getProductId());
        if (null == $product) {
            return $this->findView('error');
        }
        $data = array();
        $data['zm_product'] = $product;

        $resultList = ZMLoader::make("ResultList");
        $resultSource = ZMLoader::make("ObjectResultSource", 'Review', ZMReviews::instance(), "getReviewsForProductId", array($product->getId()));
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getPageIndex());
        $data['zm_resultList'] = $resultList;

        return $this->findView(null, $data);
    }

}

?>
