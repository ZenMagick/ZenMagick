<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.productImages
 */
class ZMProductImagesTabController extends ZMController implements ZMCatalogContentController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $productId = $request->getProductId();
        $languageId = $request->getSelectedLanguage()->getId();
        $product = ZMProducts::instance()->getProductForId($productId, $languageId);

        return $this->findView(null, array('product' => $product));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function isActive($request) {
        return 0 < $request->getProductId();
    }

    /**
     * {@inheritDoc}
     */
    public function getCatalogRequestId() {
        return 'product_images_tab';
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return _zm('Images');
    }

}
