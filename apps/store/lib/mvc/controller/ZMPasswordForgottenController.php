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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Request controller for forgotten passwords.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMPasswordForgottenController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $emailAddress = $request->getParameter('email_address');
        $account = ZMAccounts::instance()->getAccountForEmailAddress($emailAddress);
        if (null === $account || ZMAccount::REGISTERED != $account->getType()) {
            ZMMessages::instance()->error(sprintf(_zm("Sorry, there is no account with the email address '%s'."), $emailAddress));
            return $this->findView();
        }

        $newPassword = ZMAuthenticationManager::instance()->mkPassword();
        $newEncrpytedPassword = ZMAuthenticationManager::instance()->encryptPassword($newPassword);

        // update account password (encrypted)
        ZMAccounts::instance()->setAccountPassword($account->getId(), $newEncrpytedPassword);

        // send email (clear text)
        $context = array('password' => $newPassword);
        zm_mail(sprintf(_zm("Forgotten Password - %s"), ZMSettings::get('storeName')), 'password_forgotten', $context, $emailAddress, $account->getFullName());

        Runtime::getEventDispatcher()->dispatch('password_changed', new Event($this, array('controller' => $this, 'account' => $account, 'clearPassword' => $newPassword)));

        // report success
        ZMMessages::instance()->success(_zm('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}
