<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Runtime;

/**
 * Request controller for product details.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ProductInfoController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $product = null;
        $productService = $this->container->get('productService');
        $languageId = $request->getSession()->getLanguageId();
        if ($request->getProductId()) {
            $product = $productService->getProductForId($request->getProductId(), $languageId);
        } else if ($request->getModel()) {
            $product = $productService->getProductForModel($request->getModel(), $languageId);
        }

        $data = array('currentProduct' => $product);
        if (null == $product || !$product->getStatus()) {
            return $this->findView('product_not_found', $data);
        }

        if (Runtime::getSettings()->get('isLogPageStats')) {
            $productService->updateViewCount($product->getId(), $languageId);
        }

        $viewName = $this->container->get('templateManager')->getProductTemplate($product->getId());
        return $this->findView($viewName, $data);
    }

}
