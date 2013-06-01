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
namespace ZenMagick\AdminBundle\Dashboard\Widgets\Status;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Widgets\StatusCheck;

/**
 * Coupon status check.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CouponStatusCheck extends ZMObject implements StatusCheck
{
    const NEW_SIGNUP_GV_EXPIRY_THRESHOLD = 21;

    /**
     * {@inheritDoc}
     */
    public function getStatusMessages()
    {
        $messages = array();

        $languageId = $this->container->get('settingsService')->get('storeDefaultLanguageId');
        $configService = $this->container->get('configService');
        $translator = $this->container->get('translator');

        if (null != ($value = $configService->getConfigValue('NEW_SIGNUP_DISCOUNT_COUPON'))) {
            $value = $value->getValue();
            if (!empty($value)) {
              if (null != ($coupon = $this->container->get('couponService')->getCouponForId($value, $languageId))) {
                  $expiryDate = $coupon->getExpiryDate();
                  $diff = $expiryDate->diff(new \DateTime(), true);
                  $interval = (int) $diff->format('%r%a');
                  if ($interval > 0 && $interval < self::NEW_SIGNUP_GV_EXPIRY_THRESHOLD) {
                      $message = $translation->trans('Welcome Email Discount Coupon expires in %count% days.', array('%count%' => $interval));
                      $messages[] = array(StatusCheck::STATUS_NOTICE, $message);
                  }
              }
            }
        }

        if (null != ($results = \ZMRuntime::getDatabase()->fetchAll('SELECT * FROM %table.coupon_gv_queue% where release_flag = "N"')) && 0 < count($results)) {
            $url = '<a href="'.$this->container->get('router')->generate('gv_queue').'">'.$translator->trans('gift queue').'</a>';
            $message = $translator->trans('%count% item(s) in %queue_url% waiting for approval.', array('%count%' => count($results), '%queue_url%' => $url));
            $messages[] = array(StatusCheck::STATUS_NOTICE, $message);
        }

        return $messages;
    }

}
