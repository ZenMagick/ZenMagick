<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

    $zm_events->attach(new ZMEventFixes());

    /*****temp fixes for email generation.... ********/
    // set up order for order_status email
    if (null !== $zm_request->getParameter("oID") && 'update_order' == $zm_request->getParameter("action")) {
        $orderId = $zm_request->getParameter("oID");
        $zm_order = $zm_orders->getOrderForId($orderId);
        $zm_account = $zm_accounts->getAccountForId($zm_order->getAccountId());
    }
    // create zm_receiver for tell_a_friend email
    if ("tell_a_friend" == $zm_request->getPageName()) {
        $zm_receiver =& $zm_loader->create("Receiver");
        $zm_receiver->populate();
        $zm_product = null;
        if ($zm_request->getProductId()) {
            $zm_product = $zm_products->getProductForId($zm_request->getProductId());
        } else if ($zm_request->getModel()) {
            $zm_product = $zm_products->getProductForModel($zm_request->getModel());
        }
    }
    // create context for gv_send email
    if ("gv_send" == $zm_request->getPageName() && 'process' == $zm_request->getParameter('action')) {
        $zm_gvreceiver =& $zm_loader->create("GVReceiver");
        $zm_gvreceiver->populate();
    }
    /*****temp fixes for email generation.... ********/

    // simulate the number of uploads parameter for add to cart
    if ('add_product' == $zm_request->getParameter('action')) {
        $uploads = 0;
        foreach ($zm_request->getParameterMap() as $name => $value) {
            if (zm_starts_with($name, zm_setting('uploadOptionPrefix'))) {
                ++$uploads;
            }
        }
        $_GET['number_of_uploads'] = $uploads;
    }

    // security fix to allow post for login
    if ("login" == $zm_request->getPageName()) {
        // *disable* zc account create code
        $_GET['action'] = $_POST['action'];
        unset($_POST['action']);
    }

    // zc fails if UI_DATE_FORMAT changes
    define('DOB_FORMAT_STRING', UI_DATE_FORMAT);

    // non image button for gv_send edit; yuk!
    if (isset($_POST) && array_key_exists('edit', $_POST) && 'gv_send' == $zm_request->getPageName()) {
        $_POST['edit_x'] = 2;
    }

?>
