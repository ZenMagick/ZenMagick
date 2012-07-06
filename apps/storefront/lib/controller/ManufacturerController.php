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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * Request controller for manufacturer.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ManufacturerController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $viewName = 'error';
        $method = null;
        $args = null;
        $data = array();

        $languageId = $request->getSession()->getLanguageId();
        // get category

        // decide what to do
        if ($request->query->has('manufacturers_id')) {
            $method = "getProductsForManufacturerId";
            $args = array($request->query->getInt('manufacturers_id'), true, $languageId);
            $viewName = 'manufacturer';
            if (null == ($manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($request->query->getInt('manufacturers_id'), $languageId))) {
                return $this->findView('manufacturer_not_found');
            }
        }

        $resultList = null;
        if (null !== $method) {
            $resultSource = new \ZMObjectResultSource('ZMProduct', 'productService', $method, $args);
            $resultList = Runtime::getContainer()->get('ZMResultList');
            $resultList->setResultSource($resultSource);
            foreach (explode(',', Runtime::getSettings()->get('resultListProductFilter')) as $filter) {
                $resultList->addFilter(Beans::getBean($filter));
            }
            foreach (explode(',', Runtime::getSettings()->get('resultListProductSorter')) as $sorter) {
                $resultList->addSorter(Beans::getBean($sorter));
            }
            $resultList->setPageNumber($request->query->getInt('page'));
            $data['resultList'] = $resultList;
        }

        if (null != $resultList && 1 == $resultList->getNumberOfResults() && Runtime::getSettings()->get('isSkipSingleProductCategory')) {
            $results = $resultList->getResults();
            $product = array_pop($results);
            $request->query->set('productId', $product->getId());
            $viewName = 'product_info';
        }

        return $this->findView($viewName, $data);
    }

}
