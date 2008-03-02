<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Ajax controller for JSON catalog data.
 *
 * @author mano
 * @package org.zenmagick.rp.ajax.controller
 * @version $Id$
 */
class ZMAjaxCatalogController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function ZMAjaxCatalogController() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get product information for the given product id.
     *
     * @param int productd The product id.
     * @return void
     */
    function getProductForIdJSON() {
    global $zm_request;

        $productId = $zm_request->getParameter('productId', 0);

        $flatObj = $this->flattenObject(ZMProducts::instance()->getProductForId($productId),
            array('id', 'name', 'description', 'model', 
                'attributes' => array('id', 'type', 'name',
                    'values' => array('id', 'name', 'default')
                )
            )
        );
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get products for the given category id.
     *
     * @param int categoryId The categoryId id.
     * @param boolean all Admin only parameter to allow to retrieve inactive products also.
     * @return void
     */
    function getProductsForCategoryIdJSON() {
    global $zm_request;

        $categoryId = $zm_request->getParameter('categoryId', 0);
        $activeOnly = true;
        if (zm_setting('isAdmin')) {
            $activeOnly = $zm_request->getParameter('active', true);
        }

        $flatObj = $this->flattenObject(ZMProducts::instance()->getProductsForCategoryId($categoryId, $active),
            array('id', 'name', 'description', 'model', 
                'attributes' => array('id', 'type', 'name',
                    'values' => array('id', 'name', 'default')
                )
            )
        );
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
