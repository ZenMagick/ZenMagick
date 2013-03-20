<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\StoreBundle\Widgets;

use ZenMagick\Http\Widgets\Form\SelectFormWidget;

/**
 * <p>A coupon select form widget.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CouponSelectFormWidget extends SelectFormWidget
{
    /**
     * {@inheritDoc}
     */
    public function getOptions($request)
    {
        $options = parent::getOptions($request);

        // @todo remove  dependency on language and request
        $languageId = null != $request ? $request->getSelectedLanguage()->getId() : 1;
        foreach ($this->container->get('couponService')->getCoupons($languageId) as $coupon) {
            $options[$coupon->getId()] = $coupon->getName();
        }

        return $options;
    }

}
