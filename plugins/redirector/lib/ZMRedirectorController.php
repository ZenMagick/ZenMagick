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
 * Request controller of the redirector plugin.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.redirector
 */
class ZMRedirectorController extends ZMController {

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
        if (0 < $request->getProductId()) {
            if (null != ($view = $this->processMissingProduct($request))) {
                return $view;
            }
            return $this->findView('product_not_found');
        } else if (0 < $request->getCategoryId()) {
            if (null != ($view = $this->processMissingCategory($request))) {
                return $view;
            }
            return $this->findView('category_not_found');
        }

        return $this->findView('error');
    }

    /**
     * Handle product not found.
     *
     * @param ZMRequest request The current request.
     * @return ZMView A response view or <code>null</code> if no useful response possible.
     */
    protected function processMissingProduct($request) {
        $product = null;
        $productService = $this->container->get('productService');
        $languageId = $request->getSession()->getLanguageId();
        // try to find product based on the current request
        if ($request->getProductId()) {
            $product = $productService->getProductForId($request->getProductId(), $languageId);
        } else if ($request->getModel()) {
            $product = $productService->getProductForModel($request->getModel(), $languageId);
        }

        if (null != $product) {
            // check for redirect mappings
            $productMappings = ZMSettings::get('plugins.redirector.productMappings', array());
            if (array_key_exists($product->getId(), $productMappings)) {
                $view =  new ZMRedirectView();
                $view->setRequestId('product_info');
                $view->setParameter('productId='.$productMappings[$product->getId()]);
                return $view;
            }
        }

        return null;
    }

    /**
     * Handle category not found.
     *
     * @param ZMRequest request The current request.
     * @return ZMView A response view or <code>null</code> if no useful response possible.
     */
    protected function processMissingCategory($request) {
        $categoryService = $this->container->get('categoryService');

        if (null == ($category = $categoryService->getCategoryForId($request->getCategoryId(), $request->getSession()->getLanguageId()))) {
            return null;
        }

        // check for redirect mappings
        $categoryMappings = ZMSettings::get('plugins.redirector.categoryMappings', array());
        if (array_key_exists($category->getId(), $categoryMappings)) {
            // find new category
            $newCategoryId = $categoryMappings[$category->getId()];
            if (null != ($newCategory = $categoryService->getCategoryForId($newCategoryId, $request->getSession()->getLanguageId()))) {
                $view =  new ZMRedirectView();
                $view->setRequestId('category');
                $view->setParameter($newCategory->getPath());
                return $view;
            }
        }

        return null;
    }

}
