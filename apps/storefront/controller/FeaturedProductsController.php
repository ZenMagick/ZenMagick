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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Beans;

/**
 * Request controller for featured products.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FeaturedProductsController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $settingsService = $this->container->get('settingsService');

        $resultSource = new \ZMObjectResultSource('ZMProduct', 'productService', "getFeaturedProducts", array($request->attributes->get('categoryId'), 0));
        $resultList = Beans::getBean('ZMResultList');
        $resultList->setResultSource($resultSource);
        foreach (explode(',', $settingsService->get('resultListProductFilter')) as $filter) {
            $resultList->addFilter(Beans::getBean($filter));
        }
        foreach (explode(',', $settingsService->get('resultListProductSorter')) as $sorter) {
            $resultList->addSorter(Beans::getBean($sorter));
        }
        $resultList->setPageNumber($request->query->getInt('page'));

        return $this->findView(null, array('resultList' => $resultList));
    }

}
