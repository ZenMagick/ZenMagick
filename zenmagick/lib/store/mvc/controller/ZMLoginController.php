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
 * Request controller for login.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMLoginController.php 2346 2009-06-29 02:39:42Z dermanomann $
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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    public function process($request) { 
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        return parent::process($request);
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
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
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost($request) {
        $session = $request->getSession();

        if (!$session->isValid()) {
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
            ZMMessages::instance()->error(zm_l10n_get('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        $password = $request->getParameter('password');
        if (!ZMAuthenticationManager::instance()->validatePassword($password, $account->getPassword())) {
            ZMMessages::instance()->error(zm_l10n_get('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        if (!$session->registerAccount($account, $this)) {
            return $this->findView();
        }

        $followUpUrl = $session->getLoginFollowUp();
        if (null == $followUpUrl) {
            $followUpUrl = $session->getValue(self::$KEY_REDIRECT);
            $session->removeValue(self::$KEY_REDIRECT);
        }

        return $this->findView('success', array(), array('url' => $followUpUrl));
    }

}

?>
