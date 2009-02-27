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
 * Request controller for account password page.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAccountPasswordController extends ZMController {

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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        return parent::process();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        if (!$this->validate('account_password')) {
            return $this->findView();
        }

        $account = ZMRequest::getAccount();
        if (null == $account) {
            ZMLogging::instance()->log('could not access session account', ZMLogging::ERROR);
            return $this->findView('error');
        }

        $oldPassword = ZMRequest::getParameter('password_current');
        $newPassword = ZMRequest::getParameter('password_new');
        $confirmPassword = ZMRequest::getParameter('password_confirmation');

        if (!ZMAuthenticationManager::instance()->validatePassword($oldPassword, $account->getPassword())) {
            ZMMessages::instance()->error(zm_l10n_get('Your current password did not match the password in our records. Please try again.'));
            return $this->findView();
        }

        // update password
        $newEncrpytedPassword = ZMAuthenticationManager::instance()->encryptPassword($newPassword);
        ZMAccounts::instance()->setAccountPassword($account->getId(), $newEncrpytedPassword);

        ZMEvents::instance()->fireEvent($this, ZMEvents::PASSWORD_CHANGED, array(
                'controller' => $this, 
                'account' => $account, 
                'clearPassword' => $newPassword
            )
        );

        ZMMessages::instance()->success(zm_l10n_get('Your password has been updated.'));

        return $this->findView('success');
    }

}

?>
