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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * Request controller for specials.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SpecialsController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $resultSource = new \ZMObjectResultSource('ZMProduct', 'productService', "getSpecials", 0);
        $resultList = Runtime::getContainer()->get('ZMResultList');
        $resultList->setResultSource($resultSource);
        foreach (explode(',', Runtime::getSettings()->get('resultListProductFilter')) as $filter) {
            $resultList->addFilter(Beans::getBean($filter));
        }
        foreach (explode(',', Runtime::getSettings()->get('resultListProductSorter')) as $sorter) {
            $resultList->addSorter(Beans::getBean($sorter));
        }
        $resultList->setPageNumber($request->query->getInt('page'));

        return $this->findView(null, array('resultList' => $resultList));
    }

}
