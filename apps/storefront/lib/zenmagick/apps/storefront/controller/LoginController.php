<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\storefront\controller;


/**
 * Request controller for login.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class LoginController extends \ZMController {
    const KEY_REDIRECT = 'loginRedirect';


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
            $session->setValue(self::KEY_REDIRECT, $redirect);
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
            $session->setValue(self::KEY_REDIRECT);
            return $this->findView('cookie_usage');
        }

        if ($session->isRegistered()) {
            // already logged in
            $session->setValue(self::KEY_REDIRECT);
            return $this->findView('account');
        }
        if (!$this->validate($request, 'login')) {
            return $this->findView();
        }

        $emailAddress = $request->getParameter('email_address');
        $account = $this->container->get('accountService')->getAccountForEmailAddress($emailAddress);
        if (null === $account) {
            $this->messageService->error(_zm('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        $password = $request->getParameter('password');
        if (!$this->container->get('authenticationManager')->validatePassword($password, $account->getPassword())) {
            $this->messageService->error(_zm('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        if (!$session->registerAccount($account, $request, $this)) {
            return $this->findView();
        }
        $session->regenerate();

        $stickyUrl = $request->getFollowUpUrl();
        if (null == $stickyUrl) {
            $stickyUrl = $session->getValue(self::KEY_REDIRECT);
            $session->setValue(self::KEY_REDIRECT);
        }
        if (null == $stickyUrl) {
            $stickyUrl = $lastUrl;
        }

        return $this->findView('success', array(), array('url' => $stickyUrl));
    }

}
