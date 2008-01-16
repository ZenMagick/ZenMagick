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
 * Request controller for gv confirmation page.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMGvSendConfirmController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMGvSendConfirmController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMGvSendConfirmController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request;

        $action = $zm_request->getParameter('action');
        $this->exportGlobal("zm_account", $zm_request->getAccount());
        $this->exportGlobal("zm_gvreceiver", $this->create("GVReceiver"));
        $this->exportGlobal("zm_coupon", $this->create("Coupon", 0, ''));

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_runtime, $zm_coupons, $zm_messages;

        if (null != $zm_request->getParameter('edit')) {
            return $this->findView('edit');
        }

        $gvreceiver = $this->create("GVReceiver");
        $gvreceiver->populate();

        // revalidate
        if (!$this->validate('gvreceiverObject', $gvreceiver)) {
            return $this->findView('edit');
        }

        // the sender account
        $account = $zm_request->getAccount();
        // current balance
        $balance = $account->getVoucherBalance();
        // coupon amount
        $amount = $gvreceiver->getAmount(); 

        $currentCurrencyCode = $zm_runtime->getCurrencyCode();
        if (zm_setting('defaultCurrency') != $currentCurrencyCode) {
            // need to convert amount to default currency as GV values are in default currency
            $currency = $zm_currencies->getCurrencyForCode($currentCurrencyCode);
            $amount = $currency->convertFrom($amount);
        }

        // update balance
        $newBalance = $balance - $amount;
        $zm_coupons->setVoucherBalanceForAccountId($account->getId(), $newBalance);

        // create the new voucher
        $couponCode = $zm_coupons->createCouponCode($account->getEmail());
        $coupon = $zm_coupons->createCoupon($couponCode, $amount, ZM_COUPON_TYPPE_GV);

        // create coupon tracker
        $zm_coupons->createCouponTracker($coupon, $account, $gvreceiver);


        // create gv_send email
        $context = array('zm_account' => $account, 'zm_gvreceiver' => $gvreceiver, 'zm_coupon' => $coupon, 'office_only_html' => '', 'office_only_text' => '');
        zm_mail(zm_l10n_get("A gift from %s", $account->getFullName()), 'gv_send', $context, $gvreceiver->getEmail());
        if (zm_setting('isEmailAdminGvSend')) {
            // store copy
            $session = $zm_request->getSession();
            $context = zm_email_copy_context($account->getFullName(), $account->getEmail(), $session);
            $context['zm_account'] = $account;
            $context['zm_gvreceiver'] = $gvreceiver;
            $context['zm_coupon'] = $coupon;
            zm_mail(zm_l10n_get("[GIFT CERTIFICATE] A gift from %s", $account->getFullName()), 'gv_send', $context, zm_setting('emailAdminGvSend'));
        }

        $zm_messages->success(zm_l10n_get("Gift Certificate successfully send!"));

        return $this->findView('success');
    }

}

?>
