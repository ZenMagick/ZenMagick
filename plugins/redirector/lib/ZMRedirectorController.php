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


/**
 * Request controller of the redirector plugin.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.redirector
 */
class ZMRedirectorController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (0 < $request->query->get('productId')) {
            if (null != ($view = $this->processMissingProduct($request))) {
                return $view;
            }
            return $this->findView('product_not_found');
        } else if (0 < $request->attributes->get('categoryId')) {
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
     * @param zenmagick\http\Request request The current request.
     * @return View A response view or <code>null</code> if no useful response possible.
     */
    protected function processMissingProduct($request) {
        $product = null;
        $productService = $this->container->get('productService');
        $languageId = $request->getSession()->getLanguageId();
        // try to find product based on the current request
        if ($request->query->get('productId')) {
            $product = $productService->getProductForId($request->query->get('productId'), $languageId);
        } else if ($request->query->has('model')) {
            $product = $productService->getProductForModel($request->query->get('model'), $languageId);
        }

        if (null != $product) {
            // check for redirect mappings
            $productMappings = $this->container->get('settingsService')->get('plugins.redirector.productMappings', array());
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
     * @param zenmagick\http\Request request The current request.
     * @return View A response view or <code>null</code> if no useful response possible.
     */
    protected function processMissingCategory($request) {
        $categoryService = $this->container->get('categoryService');

        if (null == ($category = $categoryService->getCategoryForId($request->attributes->get('categoryId'), $request->getSession()->getLanguageId()))) {
            return null;
        }

        // check for redirect mappings
        $categoryMappings = $this->container->get('settingsService')->get('plugins.redirector.categoryMappings', array());
        if (array_key_exists($category->getId(), $categoryMappings)) {
            // find new category
            $newCategoryId = $categoryMappings[$category->getId()];
            if (null != ($newCategory = $categoryService->getCategoryForId($newCategoryId, $request->getSession()->getLanguageId()))) {
                $view =  new ZMRedirectView();
                $view->setRequestId('category');
                $view->setParameter('cPath='.implode('_', $newCategory->getPath()));
                return $view;
            }
        }

        return null;
    }

}
