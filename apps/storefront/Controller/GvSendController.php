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
namespace ZenMagick\apps\storefront\Controller;

use ZenMagick\StoreBundle\Entity\Coupons\Coupon;

/**
 * Request controller for gv send page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GvSendController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, array('currentAccount' => $this->getUser());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $gvReceiver = $this->getFormData($request);

        // back from confirmation to edit or not valid
        if (null != $request->request->get('edit')) {
            return $this->findView();
        }

        $data = array();
        $data['currentAccount'] = $this->getUser();
        // to fake the email content display
        $coupon = new Coupon();
        $coupon->setCode( _zm('THE_COUPON_CODE'));
        $data['currentCoupon'] = $coupon;

        return $this->findView('success', $data);
    }

}
