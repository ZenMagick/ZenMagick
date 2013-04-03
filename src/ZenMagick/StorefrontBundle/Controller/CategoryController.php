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

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Request controller for categories.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CategoryController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $viewName = 'error';
        $method = null;
        $args = null;
        $data = array();

        $languageId = $request->getSession()->getLanguageId();
        // get category

        // decide what to do
        if ($request->attributes->has('cPath')) {
            $method = "getProductsForCategoryId";
            $args = array($request->attributes->get('categoryId'), true, $request->getSession()->getLanguageId());
            $viewName = 'category_list';
            if (null == ($category = $this->container->get('categoryService')->getCategoryForId($request->attributes->get('categoryId'), $languageId)) || !$category->isActive()) {
                return $this->findView('category_not_found');
            }
        } elseif ($request->query->has('manufacturers_id')) {
            $method = "getProductsForManufacturerId";
            $args = array($request->query->getInt('manufacturers_id'), true, $languageId);
            $viewName = 'manufacturer';
            if (null == ($manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($request->query->getInt('manufacturers_id'), $languageId))) {
                return $this->findView('manufacturer_not_found');
            }
        }

        $settingsService = $this->container->get('settingsService');
        $resultList = null;
        if (null !== $method) {
            $resultSource = new \ZMObjectResultSource('ZenMagick\StoreBundle\Entity\Product', 'productService', $method, $args);
            $resultList = Beans::getBean('ZMResultList');
            $resultList->setResultSource($resultSource);
            foreach (explode(',', $settingsService->get('resultListProductFilter')) as $filter) {
                $resultList->addFilter(Beans::getBean($filter));
            }
            foreach (explode(',', $settingsService->get('resultListProductSorter')) as $sorter) {
                $resultList->addSorter(Beans::getBean($sorter));
            }
            $resultList->setPageNumber($request->query->getInt('page'));
            $data['resultList'] = $resultList;
        }

        if ($viewName == "category_list"
            && ((null == $resultList || !$resultList->hasResults() || (null != $category && $category->hasChildren()))
                && $settingsService->get('isUseCategoryPage'))) {
            $viewName = 'category';
        }

        if (null != $category) {
            $data['currentCategory'] = $category;
        }

        if (null != $resultList && 1 == $resultList->getNumberOfResults() && $settingsService->get('isSkipSingleProductCategory')) {
            $results = $resultList->getResults();
            $product = array_pop($results);
            $request->query->set('productId', $product->getId());
            $viewName = 'product_info';
        }

        return $this->findView($viewName, $data);
    }

}
