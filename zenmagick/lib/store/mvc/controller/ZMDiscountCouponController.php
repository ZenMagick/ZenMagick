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
 * Controller for coupon code lookup page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMDiscountCouponController.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMDiscountCouponController extends ZMController {

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
    public function handleRequest($request) { 
        $request->getCrumbtrail()->addCrumb($request->getToolbox()->utils->getTitle(null, false));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $data = array();
        $viewName = null;
        $code = $request->getParameter('lookup_discount_coupon');
        if (null == $code) {
            ZMMessages::instance()->warn(zm_l10n_get(zm_l10n_get("Please enter a coupon code.")));
        } else {
            $coupon = ZMCoupons::instance()->getCouponForCode($code);
            if (null == $coupon) {
                ZMMessages::instance()->error(zm_l10n_get("'%s' does not appear to be a valid Coupon Redemption Code.", $code));
                $data['zm_coupon_code'] = $code;
            } else {
                $data['zm_coupon'] = $coupon;
                $viewName = 'info';
            }
        }

        return $this->findView($viewName, $data);
    }

}

?>
