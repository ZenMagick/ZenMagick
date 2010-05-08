<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Request controller for gv redeem page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMGvRedeemController extends ZMController {

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
    public function processGet($request) {
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());

        $gvRedeem = $this->getFormData($request);

        //XXX: fix for gv_mail generated URLs
        if (ZMLangUtils::isEmpty($gvRedeem->getCouponCode())) {
          if (null != ($gvNo = $request->getParameter('gv_no'))) {
              $gvRedeem->setCouponCode($gvNo);
          }
        }

        if (!ZMLangUtils::isEmpty($gvRedeem->getCouponCode())) {
            // only try to redeem if code given - people might browse the page without code parameter...
            $coupon = ZMCoupons::instance()->getCouponForCode($gvRedeem->getCouponCode(), $request->getSession()->getLanguageId());
            if (null != $coupon && ZMCoupons::TYPPE_GV == $coupon->getType() && ZMCoupons::instance()->isCouponRedeemable($coupon->getId())) {
                // all good, set amount
                $gvRedeem->setAmount($coupon->getAmount());
                $gvRedeem->setRedeemed(true);
                // TODO: remote address
                ZMCoupons::instance()->redeemCoupon($coupon->getId(), $request->getAccountId());
            } else {
                // not redeemable
                ZMMessages::instance()->error(zm_l10n_get('The provided gift voucher code seems to be invalid!'));
            }
        }

        return $this->findView();
    }

}
