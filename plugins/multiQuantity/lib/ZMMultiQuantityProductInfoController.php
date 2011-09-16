<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Custom controller to handle multi quantity requests.
 *
 * @author mano
 * @package org.zenmagick.plugins.multiQuantity
 */
class ZMMultiQuantityProductInfoController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('multi_quantity_product_info');
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
    public function processPost($request) {
        $productId = $request->getProductId();
        // prepare attributes
        $multiQuantityId = $request->getParameter(MULTI_QUANTITY_ID);
        // id is the shared form field for all attributes
        $attributes = $request->getParameter('id');
        $multiQty = $attributes[$multiQuantityId];
        unset($attributes[$multiQuantityId]);

        // add each qty
        $addedSome = false;
        foreach ($multiQty as $id => $qty) {
            if (!empty($qty) && 0 < $qty) {
                $addedSome = true;
                $attributes[$multiQuantityId] = $id;
                $request->getShoppingCart()->addProduct($productId, $qty, $attributes);
            }
        }

        if (!$addedSome) {
            $this->messageService->error(_zm('Quantity missing - no product(s) added'));
        }

        return $this->findView('success');
    }

}
