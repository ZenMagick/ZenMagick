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
 * Controller for coupon code lookup page.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMDiscountCouponController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMDiscountCouponController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMDiscountCouponController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));

        return parent::process();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_coupons, $zm_messages;

        $viewName = null;
        $code = $zm_request->getParameter('lookup_discount_coupon');
        if (null == $code) {
            $zm_messages->warn(zm_l10n_get(zm_l10n_get("Please enter a coupon code.")));
        } else {
            $coupon = $zm_coupons->getCouponForCode($code);
            if (null == $coupon) {
                $zm_messages->error(zm_l10n_get("'%s' does not appear to be a valid Coupon Redemption Code.", $code));
                $this->exportGlobal("zm_coupon_code", $code);
            } else {
                $this->exportGlobal("zm_coupon", $coupon);
                $viewName = 'info';
            }
        }

        return $this->findView($viewName);
    }

}

?>
