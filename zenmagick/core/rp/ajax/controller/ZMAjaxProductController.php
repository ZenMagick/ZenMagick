<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Ajax controller for JSON product data.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.ajax.controller
 * @version $Id$
 */
class ZMAjaxProductController extends ZMAjaxController {

    /**
     * Default c'tor.
     */
    function ZMAjaxProductController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAjaxProductController();
    }

    /**
     * Default d'tor.
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
    global $zm_request, $zm_products;

        $productId = $zm_request->getParameter('productId', 0);

        $flatObj = $this->flattenObject($zm_products->getProductForId($productId), array('id', 'name', 'description', 'model', 
          'attributes' => array('id', 'type', 'name',
            'values' => array('id', 'name', 'default')
        )));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get product attributes for the given product id.
     *
     * @param int productd The product id.
     * @return void
     */
    function getAttributesForProductIdJSON() {
    global $zm_request, $zm_attributes;

        $productId = $zm_request->getParameter('productId', 0);

        $flatObj = $this->flattenObject($zm_products->getProductForId($productId), array('id', 'name', 'description', 'model'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }


}

?>
