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
namespace zenmagick\plugins\multiQuantity;

use Plugin;
use zenmagick\base\Runtime;

// The form field name indicating the attribute id used for multi qty
define('MULTI_QUANTITY_ID', 'multi_qty_id');


/**
 * Plugin implementing multi quantity product add for attributed products.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MultiQuantityPlugin extends Plugin {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Multi Quantity', 'Multi Quantity "Add Product" on a single attribute', '${plugin.version}');
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Stop zen-cart processing multi quantity requests.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        if (null != $request->getParameter(MULTI_QUANTITY_ID)) {
            // this is a multi quantity request, so leave it to the custom controller to handle
            unset($_GET['action']);

            // create mapping for lookup
            \ZMUrlManager::instance()->setMapping('product_info', array('controller' => 'ZMMultiQuantityProductInfoController'));

            // add own mapping
            if ($this->container->get('settingsService')->get('isShowCartAfterAddProduct', true)) {
                $mapping = array('success' => array(
                    'view' => 'redirect://shopping_cart'
                ));
            } else {
                $mapping = array('success' => array(
                    'view' => 'redirect://product_info&products_id='.$request->getProductId()
                ));
            }

            \ZMUrlManager::instance()->setMapping('multi_quantity_product_info', $mapping);
        }
    }

}
