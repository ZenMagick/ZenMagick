<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Request controller for account creation page.
 *
 * <p>The <em>createDefaultAddress</em> property can be used to control whether or not
 * to create a default address entry in the address book. Obviously, the validation rules
 * for the registration form need to be adjusted accordingly.</p>
 *
 * <p>The property may be set by specifying a controllerDefinition value in the <em>URL mapping</em>
 * like this:</p>
 * <p><code>ZMUrlMapper::instance()->setMappingInfo('create_account', array('controllerDefinition' => 'CreateAccountController#createDefaultAddress=false'));</code></p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCreateAccountController extends ZMController {
    private $createDefaultAddress_;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->createDefaultAddress_ = true;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Set create default address flag.
     *
     * @param boolean value The new value.
     */
    public function setCreateDefaultAddress($value) {
        // make sure we convert to boolean; typically this would be set via a bean definition
        $this->createDefaultAddress_ = ZMTools::asBoolean($value);
        ZMLogging::instance()->log('createDefaultAddress set to: '.$this->createDefaultAddress_, ZMLogging::TRACE);
    }

    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    public function process() { 
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet() {
        $account = ZMLoader::make("Account");
        $account->populate();
        $address = ZMLoader::make("Address");
        $address->populate();

        $this->exportGlobal("zm_account", $account);
        $this->exportGlobal("zm_address", $address);

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost() {
        $account = ZMLoader::make("Account");
        $account->populate();

        $address = ZMLoader::make("Address");
        $address->populate();

        if (!$this->validate('create_account')) {
            $this->exportGlobal("zm_account", $account);
            $this->exportGlobal("zm_address", $address);
            return $this->findView();
        }

        // hen and egg...
        $account->setPassword(ZMAuthenticationManager::instance()->encryptPassword(ZMRequest::getParameter('password')));
        $account = ZMAccounts::instance()->createAccount($account);

        if ($this->createDefaultAddress_) {
            $address->setAccountId($account->getId());
            $address = ZMAddresses::instance()->createAddress($address);

            $account->setDefaultAddressId($address->getId());
            ZMAccounts::instance()->updateAccount($account);
        }

        // here we have a proper account, so time to let other know about it
        ZMEvents::instance()->fireEvent($this, ZMEvents::CREATE_ACCOUNT, array('controller' => $this, 'account' => $account));
        // in case it got changed
        ZMAccounts::instance()->updateAccount($account);

        $session = ZMRequest::getSession();
        $session->recreate();
        $session->setAccount($account);
        $session->restoreCart();

        $this->exportGlobal("zm_account", $account);

        // account email
        $context = array('zm_account' => $account, 'office_only_html' => '', 'office_only_text' => '');
        zm_mail(zm_l10n_get("Welcome to %s", ZMSettings::get('storeName')), 'welcome', $context, $account->getEmail(), $account->getFullName());
        if (ZMSettings::get('isEmailAdminCreateAccount')) {
            // store copy
            $context = ZMToolbox::instance()->macro->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $session);
            $context['zm_account'] = $account;
            zm_mail(zm_l10n_get("[CREATE ACCOUNT] Welcome to %s", ZMSettings::get('storeName')), 'welcome', $context, ZMSettings::get('emailAdminCreateAccount'));
        }

        ZMMessages::instance()->success(zm_l10n_get("Thank you for signing up"));

        $followUpUrl = $session->getLoginFollowUp();
        return $this->findView('success', array('url' => $followUpUrl));
    }

}

?>
