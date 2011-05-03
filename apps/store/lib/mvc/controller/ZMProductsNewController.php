<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php


/**
 * Request controller for new products.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMProductsNewController extends ZMController {

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
        // crumbtrail handling
        $request->getToolbox()->crumbtrail->addCategoryPath($request->getCategoryPathArray());
        $request->getToolbox()->crumbtrail->addManufacturer($request->getManufacturerId());
        $request->getToolbox()->crumbtrail->addCrumb("New Products");

        $resultList = ZMBeanUtils::getBean("ZMResultList");
        $resultSource = ZMLoader::make("ZMObjectResultSource", 'Product', ZMProducts::instance(), "getNewProducts");
        $resultList->setResultSource($resultSource);
        foreach (explode(',', ZMSettings::get('resultListProductFilter')) as $filter) {
            $resultList->addFilter(ZMBeanUtils::getBean($filter));
        }
        foreach (explode(',', ZMSettings::get('resultListProductSorter')) as $sorter) {
            $resultList->addSorter(ZMBeanUtils::getBean($sorter));
        }
        $resultList->setPageNumber($request->getPageIndex());

        return $this->findView(null, array('resultList' => $resultList));
    }

}
