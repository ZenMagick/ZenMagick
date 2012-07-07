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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\http\Request;
use zenmagick\http\session\Session;
use zenmagick\http\view\ModelAndView;

/**
 * Coupon help controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CouponHelpController extends \ZMController {

    /**
     * Show.
     */
    public function show($cID, Request $request, Session $session) {
        $coupon = $this->container->get('couponService')->getCouponForId($request->getParameter('cID'), $session->getLanguageId());
        return new ModelAndView(null, array('coupon' => $coupon));
    }

}
