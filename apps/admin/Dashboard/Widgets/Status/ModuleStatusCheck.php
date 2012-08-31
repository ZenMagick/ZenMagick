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
namespace ZenMagick\apps\admin\Dashboard\Widgets\Status;

use DateTime;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\apps\store\widgets\StatusCheck;

/**
 * Module status check.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ModuleStatusCheck extends ZMObject implements StatusCheck {

    /**
     * {@inheritDoc}
     */
    public function getStatusMessages() {
        $messages = array();

        $configService = $this->container->get('configService');
        if (null != ($value = $configService->getConfigValue('MODULE_PAYMENT_INSTALLED'))) {
            $value = $value->getValue();
            if (empty($value)) {
                $messages[] = array(StatusCheck::STATUS_NOTICE, _zm('You have no payment modules activated. Please go to Configuration->Modules->Payment to configure.'));
            }
        }
        if (null != ($value = $configService->getConfigValue('MODULE_SHIPPING_INSTALLED'))) {
            $value = $value->getValue();
            if (empty($value)) {
                $messages[] = array(StatusCheck::STATUS_NOTICE, _zm('You have no shipping modules activated. Please go to Configuration->Modules->Shipping to configure.'));
            }
        }

        // payment module test modes
        if (defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && defined('MODULE_PAYMENT_PAYPAL_TESTING') && (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'true' || MODULE_PAYMENT_PAYPAL_TESTING == 'Test')) {
            $messages[] = array(StatusCheck::STATUS_NOTICE, _zm('PayPal is in testing mode.'));
        }
        if ((defined('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS') && MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS == 'True'
          && defined('MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE') && MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Test')
          || (defined('MODULE_PAYMENT_AUTHORIZENET_STATUS') && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True'
              && defined('MODULE_PAYMENT_AUTHORIZENET_TESTMODE') && MODULE_PAYMENT_AUTHORIZENET_TESTMODE =='Test' ) ) {
            $messages[] = array(StatusCheck::STATUS_NOTICE, _zm('AuthorizeNet is in testing mode.'));
        }
        if (defined('MODULE_SHIPPING_USPS_SERVER') && MODULE_SHIPPING_USPS_SERVER == 'test' ) {
            $messages[] = array(StatusCheck::STATUS_NOTICE, _zm('USPS is in testing mode.'));
        }

        return $messages;
    }

}
