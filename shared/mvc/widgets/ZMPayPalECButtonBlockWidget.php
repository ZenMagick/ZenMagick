<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * PayPal express checkout block widget.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.widgets
 */
class ZMPayPalECButtonBlockWidget extends ZMWidget {

    /**
     * {@inheritDoc}
     */
    public function render($request, $view) {
        ob_start();

        if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True') {
            global $order, $db, $currencies;
            include(DIR_FS_CATALOG . DIR_WS_MODULES .  'payment/paypal/tpl_ec_button.php');
        }

        return ob_get_clean();
    }

}
