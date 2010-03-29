<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Request controller for product comparison.
 *
 * <p>Expects a number of product ids in the request parameter <code>compareId</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMProductComparisonController extends ZMController {

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
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Compare Products");

        $product = null;
        $productIds = $request->getParameter("compareId");
        $productList = array();
        foreach ($productIds as $productId) {
            $product = ZMProducts::instance()->getProductForId($productId);
            array_push($productList, $product);
            if (3 == count($productList))
                break;
        }
        if (3 < count($productIds)) {
            ZMMessages::instance()->warn(zm_l10n_get("You can't compare more that 3 products - displaying first three."));
        }

        return $this->findView(null, array('resultList' => $resultList));
    }

}
