<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * <p><code>ZMUrlManager::instance()->setMapping('create_account', array('controller' => 'CreateAccountController#createDefaultAddress=false'), false);</code></p>
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
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
        $this->createDefaultAddress_ = ZMLangUtils::asBoolean($value);
        ZMLogging::instance()->log('createDefaultAddress set to: '.$this->createDefaultAddress_, ZMLogging::TRACE);
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) { 
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->url('account', '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $registration = $this->getFormData($request);

        $clearPassword = $registration->getPassword();
        $account = $registration->getAccount();
        $account->setPassword(ZMAuthenticationManager::instance()->encryptPassword($clearPassword));
        $account = ZMAccounts::instance()->createAccount($account);

        $address = null;
        if ($this->createDefaultAddress_) {
            // account and address refer to each other...
            $address = $registration->getAddress();
            $address->setPrimary(true);
            $address->setAccountId($account->getId());
            $address = ZMAddresses::instance()->createAddress($address);
            $account->setDefaultAddressId($address->getId());
            ZMAccounts::instance()->updateAccount($account);
        }

        // here we have a proper account, so time to let other know about it
        ZMEvents::instance()->fireEvent($this, Events::CREATE_ACCOUNT, array(
                'request' => $request, 
                'controller' => $this, 
                'account' => $account, 
                'address' => $address, 
                'clearPassword' => $clearPassword
            )
        );

        // in case it got changed
        ZMAccounts::instance()->updateAccount($account);
        if (null != $address) {
            ZMAddresses::instance()->updateAddress($address);
        }

        $session = $request->getSession();
        $session->recreate();
        $session->setAccount($account);
        $session->restoreCart();

        // account email
        $context = array('currentAccount' => $account, 'office_only_html' => '', 'office_only_text' => '');
        zm_mail(sprintf(_zm("Welcome to %s"), ZMSettings::get('storeName')), 'welcome', $context, $account->getEmail(), $account->getFullName());
        if (ZMSettings::get('isEmailAdminCreateAccount')) {
            // store copy
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $session);
            $context['currentAccount'] = $account;
            zm_mail(sprintf(_zm("[CREATE ACCOUNT] Welcome to %s"), ZMSettings::get('storeName')), 'welcome', $context, ZMSettings::get('emailAdminCreateAccount'));
        }

        ZMMessages::instance()->success(_zm("Thank you for signing up"));

        $stickyUrl = $request->getFollowUpUrl();
        return $this->findView('success', array('currentAccount' => $account), array('url' => $stickyUrl));
    }

}
