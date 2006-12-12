<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.uip.controller
 * @version $Id$
 */
class ZMDiscountCouponController extends ZMController {

    // create new instance
    function ZMDiscountCouponController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMDiscountCouponController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));

        return true;
    }

    // process a POST request
    function processPost() {
    global $zm_request, $zm_crumbtrail, $zm_coupons, $zm_messages;

        $zm_crumbtrail->addCrumb(zm_nice_page_name());

        $code = $zm_request->getRequestParameter('lookup_discount_coupon');
        if (null == $code) {
            $zm_messages->add(zm_l10n_get("Please enter a coupon code."), "warn");
        } else {
            $coupon = $zm_coupons->getCouponForCode($code);
            if (null == $coupon) {
                $zm_messages->add(zm_l10n_get("'%s' does not appear to be a valid Coupon Redemption Code.", $code));
                $this->exportGlobal("zm_coupon_code", $code);
            } else {
                $this->exportGlobal("zm_coupon", $coupon);
                $this->setResponseView(new ZMView("discount_coupon_info", "discount_coupon_info"));
            }
        }

        return new ZMThemeView('discount_coupon');
    }

}

?>
