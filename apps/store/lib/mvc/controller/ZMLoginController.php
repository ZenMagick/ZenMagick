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
 * Request controller for login.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMLoginController extends ZMController {
    private static $KEY_REDIRECT = 'loginRedirect';

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
    public function processGet($request) {
        $session = $request->getSession();
        if ($session->isRegistered()) {
            // can't get any better than this!
            return $this->findView('index');
        }

        $redirect = $request->getParameter('redirect');
        if (null != $redirect) {
            $session->setValue(self::$KEY_REDIRECT, $redirect);
        }

        return parent::processGet($request);

    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $session = $request->getSession();

        // get before doing anything with the session!
        $lastUrl = $request->getLastUrl();

        if (!$session->isStarted()) {
            $session->removeValue(self::$KEY_REDIRECT);
            return $this->findView('cookie_usage');
        }

        if ($session->isRegistered()) {
            // already logged in
            $session->removeValue(self::$KEY_REDIRECT);
            return $this->findView('account');
        }

        if (!$this->validate($request, 'login')) {
            return $this->findView();
        }

        $emailAddress = $request->getParameter('email_address');
        $account = ZMAccounts::instance()->getAccountForEmailAddress($emailAddress);
        if (null === $account) {
            ZMMessages::instance()->error(_zm('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        $password = $request->getParameter('password');
        if (!ZMAuthenticationManager::instance()->validatePassword($password, $account->getPassword())) {
            ZMMessages::instance()->error(_zm('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        if (!$session->registerAccount($account, $request, $this)) {
            return $this->findView();
        }

        $stickyUrl = $request->getFollowUpUrl();
        if (null == $stickyUrl) {
            $stickyUrl = $session->getValue(self::$KEY_REDIRECT);
            $session->removeValue(self::$KEY_REDIRECT);
        }
        if (null == $stickyUrl) {
            $stickyUrl = $lastUrl;
        }

        return $this->findView('success', array(), array('url' => $stickyUrl));
    }

}
