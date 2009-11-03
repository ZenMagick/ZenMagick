<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Request controller for product details.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMProductInfoController.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMProductInfoController extends ZMController {

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
        $product = null;
        if ($request->getProductId()) {
            $product = ZMProducts::instance()->getProductForId($request->getProductId());
        } else if ($request->getModel()) {
            $product = ZMProducts::instance()->getProductForModel($request->getModel());
        }

        $data = array('zm_product' => $product);
        if (null == $product || !$product->getStatus()) {
            return $this->findView('product_not_found', $data);
        }

        if (ZMSettings::get('isLogPageStats')) {
            ZMProducts::instance()->updateViewCount($product);
        }

        // crumbtrail handling
        $request->getCrumbtrail()->addCategoryPath($request->getCategoryPathArray());
        $request->getCrumbtrail()->addManufacturer($request->getManufacturerId());
        $request->getCrumbtrail()->addProduct($product->getId());

        $viewName = ZMTemplateManager::instance()->getProductTemplate($product->getId());
        return $this->findView($viewName, $data);
    }

}

?>
