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
 * Request controller for static pages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.uip.controller
 * @version $Id$
 */
class ZMProductComparisonController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMProductComparisonController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMProductComparisonController();
    }

    function __destruct() {
    }


    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_products, $zm_messages;

        $zm_crumbtrail->addCrumb("Compare Products");

        $product = null;
        $productIds = $zm_request->getRequestParameter("compareId");
        $productList = array();
        foreach ($productIds as $productId) {
            $product = $zm_products->getProductForId($productId);
            array_push($productList, $product);
            if (3 == count($productList))
                break;
        }
        if (3 < count($productIds)) {
            $zm_messages->add(zm_l10n_get("You can't compare more that 3 products - displaying first three."), "warn");
        }

        $this->exportGlobal("zm_productList", $productList);

        return new ZMThemeView('product_comparison');
    }

}

?>
