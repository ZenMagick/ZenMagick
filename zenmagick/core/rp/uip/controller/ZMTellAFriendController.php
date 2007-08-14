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
 */
?>
<?php


/**
 * Request controller for tell a friend form.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMTellAFriendController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMTellAFriendController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMTellAFriendController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_products;

        $product = null;
        if ($zm_request->getProductId()) {
            $product = $zm_products->getProductForId($zm_request->getProductId());
        } else if ($zm_request->getModel()) {
            $product = $zm_products->getProductForModel($zm_request->getModel());
        }

        if (null == $product) {
            return $this->findView('error');
        }

        $account = $zm_request->getAccount();
        $emailMessage = $this->create("EmailMessage");
        if (null != $account) {
            $emailMessage->setFromEmail($account->getEmail());
            $emailMessage->setFromName($account->getFullName());
        }

        $this->exportGlobal("zm_emailMessage", $emailMessage);
        $this->exportGlobal("zm_product", $product);

        // crumbtrail handling
        $zm_crumbtrail->addCategoryPath($zm_request->getCategoryPathArray());
        $zm_crumbtrail->addManufacturer($zm_request->getManufacturerId());
        $zm_crumbtrail->addProduct($product->getId());
        $zm_crumbtrail->addCrumb("Tell A Friend");

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_crumbtrail, $zm_messages, $zm_products;

        $zm_crumbtrail->addCrumb("Tell A Friend");

        $emailMessage = $this->create("EmailMessage");
        $emailMessage->populate();

        if (!$this->validate('tell_a_friend')) {
            $this->exportGlobal("zm_emailMessage", $emailMessage);
            return $this->findView();
        }

        $product = null;
        if ($zm_request->getProductId()) {
            $product = $zm_products->getProductForId($zm_request->getProductId());
        } else if ($zm_request->getModel()) {
            $product = $zm_products->getProductForModel($zm_request->getModel());
        }

        if (null == $product) {
            return $this->findView('error');
        }

        $context = array('zm_emailMessage' => $emailMessage, 'zm_product' => $product, 'office_only_html' => '', 'office_only_text' => '');
        $subject = zm_l10n_get("Your friend %s has recommended this great product from %s", $emailMessage->getFromName(), zm_setting('storeName'));
        zm_mail($subject, 'tell_a_friend', $context, $emailMessage->getToEmail(), $emailMessage->getToName());
        if (zm_setting('isEmailAdminTellAFriend')) {
            // store copy
            $session = new ZMSession();
            $context = zm_email_copy_context($emailMessage->getFromName(), $emailMessage->getFromEmail(), $session);
            $context['zm_emailMessage'] = $emailMessage;
            $context['zm_product'] = $product;
            zm_mail("[TELL A FRIEND] ".$subject, 'tell_a_friend', $context, zm_setting('emailAdminTellAFriend'));
        }

        $zm_messages->success("Message send successfully");
        $emailMessage = $this->create("EmailMessage");
        $this->exportGlobal("zm_emailMessage", $emailMessage);

        return $this->findView('success', array('parameter' => 'products_id='.$product->getId()));
    }

}

?>
