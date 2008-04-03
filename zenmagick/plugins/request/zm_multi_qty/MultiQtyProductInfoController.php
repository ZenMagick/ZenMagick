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
 * Custom controller to handle multi qty posts.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_multi_qty
 * @version $Id$
 */
class MultiQtyProductInfoController extends ZMController {

    /**
     * TODO: remove
     */
    function MultiQtyProductInfoController() {
        $this->__construct();
    }

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
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_cart, $zm_messages;

        $productId = $zm_request->getProductId();
        // prepare attributes
        $multiQtyId = $zm_request->getParameter(MULTI_QUANTITY_ID);
        // id is the shared form field for all attributes
        $attributes = $zm_request->getParameter('id');
        $multiQty = $attributes[$multiQtyId];
        unset($attributes[$multiQtyId]);

        // add each qty
        $addedSome = false;
        foreach ($multiQty as $id => $qty) {
            if (!empty($qty) && 0 < $qty) {
                $addedSome = true;
                $attributes[$multiQtyId] = $id;
                $zm_cart->addProduct($productId, $qty, $attributes);
            }
        }

        if (!$addedSome) {
            $zm_messages->error(zm_l10n_get('Quantity missing - no product(s) added'));
        }

        return $this->findView('success');
    }

}

?>
