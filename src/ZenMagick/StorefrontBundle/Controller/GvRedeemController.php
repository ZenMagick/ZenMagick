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
namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\Base\Toolbox;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;
use ZenMagick\StoreBundle\Entity\Coupons\Coupon;

/**
 * Request controller for gv redeem page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GvRedeemController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $gvRedeem = $this->getFormData($request);

        $translator = $this->get('translator');
        //XXX: fix for gv_mail generated URLs
        if (Toolbox::isEmpty($gvRedeem->getCouponCode())) {
          if (null != ($gvNo = $request->getParameter('gv_no'))) {
              $gvRedeem->setCouponCode($gvNo);
          }
        }

        if (!Toolbox::isEmpty($gvRedeem->getCouponCode())) {
            $couponService = $this->container->get('couponService');
            // only try to redeem if code given - people might browse the page without code parameter...
            $coupon = $couponService->getCouponForCode($gvRedeem->getCouponCode(), $request->getSession()->getLanguageId());
            if (null != $coupon && Coupon::TYPPE_GV == $coupon->getType() && $couponService->isCouponRedeemable($coupon->getId())) {
                // all good, set amount
                $gvRedeem->setAmount($coupon->getAmount());
                $gvRedeem->setRedeemed(true);
                // TODO: remote address
                $couponService->redeemCoupon($coupon->getId(), $this->getUser()->getId());
            } else {
                // not redeemable
                $this->get('session.flash_bag')->error($translator->trans('The provided gift voucher code seems to be invalid!'));
            }
        }

        return $this->findView();
    }

}
