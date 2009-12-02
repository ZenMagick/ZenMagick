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


define('AUTO_LOGIN_COOKIE', 'auto_login');
define('AUTO_LOGIN_OPT_IN', 'autoLogin');


/**
 * Plugin to automtically login returning customers.
 *
 * @package org.zenmagick.plugins.autoLogin
 * @author DerManoMann
 * @version $Id: zm_auto_login.php 2560 2009-11-02 20:08:36Z dermanomann $
 */
class ZMAutoLoginPlugin extends Plugin implements ZMRequestHandler {
    private $cookieUpdated;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Auto Login', 'Automatically login returning customers.', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
        $this->cookieUpdated = false;
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
    public function install() {
        parent::install();

        $this->addConfigValue('Opt In', 'optIn', 'true', 'Allow users to opt in',
            'widget@BooleanFormWidget#name=optIn&default=true&label=Allow opt in&style=checkbox');
        $this->addConfigValue('Lifetime', 'lifetime', '7', 'Cookie/hash lifetime in days');
    }

    /**
     * {@inheritDoc}
     */
    public function initRequest($request) {
        ZMEvents::instance()->attach($this);

        $session = $request->getSession();
        if ('GET' == $request->getMethod() && 'logoff' != $request->getRequestId() && $session->isAnonymous()) {
            // try to login
            if (isset($_COOKIE[AUTO_LOGIN_COOKIE])) {
                // prepare cookie data
			          $cookie = explode('~~~', $_COOKIE[AUTO_LOGIN_COOKIE]);
                // need account to login
                $account = null;

                // validate using token in cookie
                $token = ZMTokens::instance()->getTokenForHash($cookie[0]);
                if (null != $token) {
                    // resource = auto_login/id/[accountId]
                    $bits = explode('/', $token->getResource());
                    if (3 == count($bits) && 'auto_login' == $bits[0] && 'id' == $bits[1]) {
                        $account = ZMAccounts::instance()->getAccountForId((int)$bits[2]);
                        // TODO: renew cookie if required
                    }
                }

                if (null != $account) {
                    if ($session->registerAccount($account, $this)) {
                        $request->redirect($request->getToolbox()->net->url(null, '', $request->isSecure()));
                    }
                } else {
                    // remove cookie
                    setcookie(AUTO_LOGIN_COOKIE, 'expired', time() - 3600);
                }
            }
        }
    }


    /**
     * Make a resource string based on the given account.
     *
     * @param ZMAccount account The current account.
     * @return string The string.
     */
    protected function getResource($account) {
        return 'auto_login/id/'.$account->getId();
    }

    /**
     * Set auto login cookie depending on optIn.
     *
     * @param ZMAccount account The account.
     * @param string optIn The users optIn preference.
     */
    protected function onOptIn($account, $optIn) {
        if (!ZMLangUtils::asBoolean($this->get('optIn')) || ZMLangUtils::asBoolean($optIn)) {
            // cookie contains token hash only
            $resource = $this->getResource($account);
            $tokens = ZMTokens::instance()->getTokenForResource($resource);
            if (0 == count($tokens)) {
                $token = ZMTokens::instance()->getNewToken($resource, 60*60*24*$this->get('lifetime'));
            } else {
                $token = $tokens[0];
            }
            $data = array($token->getHash());

            $cookie = implode('~~~', $data);
            setcookie(AUTO_LOGIN_COOKIE, $cookie, time()+60*60*24*$this->get('lifetime'));
            $this->cookieUpdated = true;
        }
    }

    /**
     * Event handler to update the cookie if required.
     *
     * @param array args Optional parameter.
     */
    public function onZMFinaliseContents($args=array()) {
        $request = $args['request'];
        $session = $request->getSession();
        if ('GET' == $request->getMethod() && $session->isRegistered()) {
            if (!$this->cookieUpdated) {
                $this->onOptIn($request->getAccount(), array_key_exists(AUTO_LOGIN_COOKIE, $_COOKIE));
            }
        }
    }

    /**
     * Event handler for create account.
     *
     * @param array args Optional parameter.
     */
    public function onZMCreateAccount($args=array()) {
        $this->onOptIn($args['account'], ZMRequest::instance()->getParameter(AUTO_LOGIN_OPT_IN));
    }

    /**
     * Event handler for login.
     *
     * @param array args Optional parameter.
     */
    public function onZMLoginSuccess($args=array()) {
        $this->onOptIn($args['account'], ZMRequest::instance()->getParameter(AUTO_LOGIN_OPT_IN));
    }

    /**
     * Event handler for logout.
     *
     * @param array args Optional parameter.
     */
    public function onZMLogoffSuccess($args=array()) {
        if (array_key_exists(AUTO_LOGIN_COOKIE, $_COOKIE)) {
            setcookie(AUTO_LOGIN_COOKIE, 'expired', time() - 3600);
            $this->cookieUpdated = true;
        }
    }

}

?>
