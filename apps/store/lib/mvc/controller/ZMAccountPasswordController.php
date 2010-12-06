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
 * Request controller for account password page.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
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
        if (!$this->validate($request, 'account_password')) {
            return $this->findView();
        }

        $account = $request->getAccount();
        if (null == $account) {
            ZMLogging::instance()->log('could not access session account', ZMLogging::ERROR);
            return $this->findView('error');
        }

        $oldPassword = $request->getParameter('password_current');
        $newPassword = $request->getParameter('password_new');
        $confirmPassword = $request->getParameter('password_confirmation');

        if (!ZMAuthenticationManager::instance()->validatePassword($oldPassword, $account->getPassword())) {
            ZMMessages::instance()->error(_zm('Your current password did not match the password in our records. Please try again.'));
            return $this->findView();
        }

        // update password
        $newEncrpytedPassword = ZMAuthenticationManager::instance()->encryptPassword($newPassword);
        ZMAccounts::instance()->setAccountPassword($account->getId(), $newEncrpytedPassword);

        ZMEvents::instance()->fireEvent($this, Events::PASSWORD_CHANGED, array(
                'controller' => $this, 
                'account' => $account, 
                'clearPassword' => $newPassword
            )
        );

        ZMMessages::instance()->success(_zm('Your password has been updated.'));

        return $this->findView('success');
    }

}
