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
 * Request controller for static pages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMProductInfoController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMProductInfoController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMProductInfoController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_products;

        $product = null;
        if ($zm_request->getProductId()) {
            $product = $zm_products->getProductForId($zm_request->getProductId());
        } else if ($zm_request->getModel()) {
            $product = $zm_products->getProductForModel($zm_request->getModel());
        }

        if (null == $product) {
            return $this->findView('error');
        }

        $this->exportGlobal("zm_product", $product);

        //$zm_products->updateViewCount($product);

        // crumbtrail handling
        $zm_crumbtrail->addCategoryPath($zm_request->getCategoryPathArray());
        $zm_crumbtrail->addManufacturer($zm_request->getManufacturerId());
        $zm_crumbtrail->addProduct($product->getId());

        $viewName = zen_get_info_page($product->getId());
        return $this->findView($viewName);
    }

}

?>
