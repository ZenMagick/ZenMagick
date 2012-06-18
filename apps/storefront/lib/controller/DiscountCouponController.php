<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\controller;


/**
 * Controller for coupon code lookup page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class DiscountCouponController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $data = array();
        $viewName = null;
        $code = $request->getParameter('couponCode');
        if (null != $code) {
            $coupon = $this->container->get('couponService')->getCouponForCode($code, $request->getSession()->getLanguageId());
            if (null == $coupon) {
                $this->messageService->error(sprintf(_zm("'%s' does not appear to be a valid Coupon Redemption Code."), $code));
                $data['currentCouponCode'] = $code;
            } else {
                $data['currentCoupon'] = $coupon;
                $viewName = 'info';
            }
        }

        return $this->findView($viewName, $data);
    }

}
