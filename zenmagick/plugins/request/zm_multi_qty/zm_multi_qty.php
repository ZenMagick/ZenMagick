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

// The form field name indicating the attribute id used for multi qty
define('MULTI_QUANTITY_ID', 'multi_qty_id');


/**
 * Plugin implementing multi qty product add for attributed products.
 *
 * @package org.zenmagick.plugins.zm_multi_qty
 * @author DerManoMann
 * @version $Id$
 */
class zm_multi_qty extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Multi Quantity', 'Multi Quantity "Add Product" on a single attribute', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // register as event listener
        $this->zcoSubscribe();

        // make sure this exists...
        if (null === ZMSettings::get('isShowCartAfterAddProduct')) {
            ZMSettings::set('isShowCartAfterAddProduct', true);
        }
    }

    /**
     * Stop zen-cart processing multi qty requests.
     */
    function onZMInitDone($args) {
        if (null != ZMRequest::getParameter(MULTI_QUANTITY_ID)) {
            // this is a multi qty add, so leave it to the custom controller to do so
            unset($_GET['action']);
            // tweak the ZM_PAGE_KEY parameter to use our custom controller
            ZMRequest::setParameter(ZM_PAGE_KEY, 'multi_qty_product_info');

            // add url mappings
            if (ZMSettings::get('isShowCartAfterAddProduct')) {
                ZMUrlMapper::instance()->setMapping('multi_qty_product_info', 'success', 'shopping_cart', 'RedirectView', 'secure=true');
            } else {
                ZMUrlMapper::instance()->setMapping('multi_qty_product_info', 'success', 'product_info', 'RedirectView', 'parameter=products_id='.ZMRequest::getProductId());
            }
        }
    }

}

?>
