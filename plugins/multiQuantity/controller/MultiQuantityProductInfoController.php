<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\plugins\multiQuantity\controller;

use ZMController;

/**
 * Custom controller to handle multi quantity requests.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MultiQuantityProductInfoController extends ZMController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('multi_quantity_product_info');
    }


    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $productId = $request->request->get('productId');
        // prepare attributes
        $multiQuantityId = $request->request->get(MULTI_QUANTITY_ID);
        // id is the shared form field for all attributes
        $attributes = $request->request->get('id');
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
