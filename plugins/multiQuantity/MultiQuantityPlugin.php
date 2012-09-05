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
namespace ZenMagick\plugins\multiQuantity;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Runtime;

// The form field name indicating the attribute id used for multi qty
define('MULTI_QUANTITY_ID', 'multi_qty_id');


/**
 * Plugin implementing multi quantity product add for attributed products.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MultiQuantityPlugin extends Plugin {

    /**
     * Stop zen-cart processing multi quantity requests.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        if (null != $request->getParameter(MULTI_QUANTITY_ID)) {
            // this is a multi quantity request, so leave it to the custom controller to handle
            //
            $request->query->remove('action');
            $request->request->remove('action');

            $urlManager = $this->container->get('urlManager');
            // create mapping for lookup
            $urlManager->setMapping('product_info',
                array('controller' => 'ZenMagick\plugins\multiQuantity\controller\MultiQuantityProductInfoController'));

            // add own mapping
            if ($this->container->get('settingsService')->get('isShowCartAfterAddProduct', true)) {
                $mapping = array('success' => array(
                    'view' => 'redirect://shopping_cart'
                ));
            } else {
                $mapping = array('success' => array(
                    'view' => 'redirect://product_info&productId='.$request->request->get('productId')
                ));
            }

            $urlManager->setMapping('multi_quantity_product_info', $mapping);
        }
    }

}
