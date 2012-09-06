<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Model\Checkout\ShoppingCart;

/**
 * Order totals.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.checkout
 */
class ZMOrderTotals extends ZMObject {
    private $orderTotals;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->orderTotals = null;
    }


    /**
     * Get zen-cart order totals.
     *
     * @param ShoppingCart $shoppingCart The current shopping cart.
     * @return array zencart order totals.
     */
    protected function getZenTotals(ShoppingCart $shoppingCart) {
    global $order, $shipping_modules;

        // save
        $otmp = $order;
        $smtmp = $shipping_modules;

        $order = new \order();
        if (!isset($shipping_modules)) {
            $ssm = array();
            if (null != ($shippingMethod = $shoppingCart->getSelectedShippingMethod())) {
                $ssm = array(
                    'id' => $shippingMethod->getShippingId(),
                    'title' => $shippingMethod->getName(),
                    'cost' => $shippingMethod->getCost()
                );
            }
            $shipping_modules = new \shipping($ssm);
        }
        $zenTotals = new \order_total();
        $zenTotals->collect_posts();
        $zenTotals->pre_confirmation_check();
        $zenTotals->process();

        // restore
        $order = $otmp;
        $shipping_modules = $smtmp;

        return $zenTotals;
    }

    /**
     * Get order totals for the given shopping cart.
     * @param ShoppingCart $shoppingCart The current shopping cart.
     * @param boolean force Optional flag to force a reload; default is <code>false</code>.
     * @return array List of <code>ZMOrderTotal</code> instances.
     */
    public function getOrderTotals(ShoppingCart $shoppingCart, $force=false) {
        if ($force || null === $this->orderTotals) {
            $this->orderTotals = array();
            if (null != ($zenTotals = $this->getZenTotals($shoppingCart))) {
                foreach ($zenTotals->modules as $module) {
                    $class = str_replace('.php', '', $module);
                    $output = $GLOBALS[$class]->output;
                    $type = substr($class, 3);
                    foreach ($output as $zenTotal) {
                        $this->orderTotals[] = new ZMOrderTotalLine($zenTotal['title'], $zenTotal['text'], $zenTotal['value'], $type);
                    }
                }
            }
        }

        return $this->orderTotals;
    }

}
