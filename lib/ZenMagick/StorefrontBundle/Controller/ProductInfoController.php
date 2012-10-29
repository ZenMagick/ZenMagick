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
namespace ZenMagick\StorefrontBundle\Controller;

/**
 * Request controller for product details.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ProductInfoController extends \ZMController
{
    /**
     * {@inheritDoc}
     *
     * Show a product based on a id or model.
     */
    public function showAction($productId)
    {
        $product = null;
        $productService = $this->container->get('productService');
        $languageId = $this->getRequest()->getSession()->getLanguageId();
        if (is_numeric($productId)) {
            $product = $productService->getProductForId($productId, $languageId);
        }
        if (!$product) { // try model
            $product = $productService->getProductForModel($productId, $languageId);
        }
        $data = array('currentProduct' => $product);
        if (null == $product || !$product->getStatus()) {
            return $this->findView('product_not_found', $data);
        }

        if ($this->container->get('settingsService')->get('isLogPageStats')) {
            $productService->updateViewCount($product->getId(), $languageId);
        }

        $viewName = $this->container->get('templateManager')->getProductTemplate($product->getId());

        return $this->findView($viewName, $data);
    }

}
