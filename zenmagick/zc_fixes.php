<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 *
 * $Id$
 */
?>
<?php

    /**
     * Simple function to check if we need zen-cart...
     */
    function zm_needs_zc() {
        $pageName = ZMRequest::getPageName();
        return (zm_is_checkout_page() && 'checkout_shipping_address' != $pageName && 'checkout_payment_address' != $pageName) 
            || zm_is_in_array($pageName, 'advanced_search_result');
    }

    // skip more zc request handling
    if (ZMSettings::get('isEnableZenMagick') && !zm_needs_zc()) {
        $code_page_directory = 'zenmagick';
    }


    ZMEvents::instance()->attach(ZMLoader::make("EventFixes"));

    /*****temp fixes for email generation.... ********/
    // set up order for order_status email
    if (null !== ZMRequest::getParameter("oID") && 'update_order' == ZMRequest::getParameter("action")) {
        $orderId = ZMRequest::getParameter("oID");
        $zm_order = ZMOrders::instance()->getOrderForId($orderId);
        $zm_account = ZMAccounts::instance()->getAccountForId($zm_order->getAccountId());
    }
    /*****temp fixes for email generation.... ********/

    // simulate the number of uploads parameter for add to cart
    if ('add_product' == ZMRequest::getParameter('action')) {
        $uploads = 0;
        foreach (ZMRequest::getParameterMap() as $name => $value) {
            if (zm_starts_with($name, ZMSettings::get('uploadOptionPrefix'))) {
                ++$uploads;
            }
        }
        $_GET['number_of_uploads'] = $uploads;
    }

?>
