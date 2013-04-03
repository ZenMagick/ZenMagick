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
 * Request controller for new products.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ProductsNewController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $resultSource = new \ZMObjectResultSource('ZenMagick\StoreBundle\Entity\Product', 'productService', "getNewProducts");
        $resultList = Beans::getBean('ZMResultList');
        $resultList->setResultSource($resultSource);
        $settingsService = $this->container->get('settingsService');
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
