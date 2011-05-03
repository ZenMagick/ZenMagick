<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 */
class ZMAutoLoginPlugin extends Plugin {
    private $cookieUpdated;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Auto Login', 'Automatically login returning customers.', '${plugin.version}');
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
        $this->setLoaderPolicy(ZMPlugin::LP_NONE);
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
            'widget@ZMBooleanFormWidget#name=optIn&default=true&label=Allow opt in&style=checkbox');
        $this->addConfigValue('Lifetime', 'lifetime', '7', 'Cookie/hash lifetime in days');
        $this->addConfigValue('URL token', 'urlToken', 'false', 'Accept token in URL query parameters',
            'widget@ZMBooleanFormWidget#name=urlToken&default=false&label=Accept URL token&style=checkbox');
        $this->addConfigValue('Expire URL token', 'expireUrlToken', 'true', 'Invalidate URL token after use',
            'widget@ZMBooleanFormWidget#name=expireUrlToken&default=true&label=Expire URL token after use&style=checkbox');
        $this->addConfigValue('URL token name', 'urlTokenName', 'token', 'Name of the URL token parameter');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Handle init request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');

        $session = $request->getSession();
        if ('GET' == $request->getMethod() && 'logoff' != $request->getRequestId() && $session->isAnonymous()) {
            if (null != ($token = $this->getRequestToken($request))) {
                // need account to login
                $account = null;

                // resource = auto_login/id/[accountId]
                $bits = explode('/', $token->getResource());
                if (3 == count($bits) && 'auto_login' == $bits[0] && 'id' == $bits[1]) {
                    $account = ZMAccounts::instance()->getAccountForId((int)$bits[2]);
                    // TODO: renew cookie if required
                }

                if (null != $account) {
                    if ($session->registerAccount($account, $request, $this)) {
                        $request->redirect($request->url(null, '', $request->isSecure()));
                    }
                } else {
                    // remove cookie
                    setcookie(AUTO_LOGIN_COOKIE, 'expired', time() - 3600);
                }
            }
        }
    }


    /**
     * Try to get a token from the current request.
     *
     * @param ZMRequest request The current request.
     * @return ZMToken A token or <code>null</code>.
     */
    protected function getRequestToken($request) {
        // try cookie first
        if (isset($_COOKIE[AUTO_LOGIN_COOKIE])) {
            // prepare cookie data
            $cookie = explode('~~~', $_COOKIE[AUTO_LOGIN_COOKIE]);
            return ZMTokens::instance()->getTokenForHash($cookie[0]);
        }

        // check for url parameter if enabled
        if ($this->get('urlToken') && null != ($hash = $request->getParameter($this->get('urlTokenName')))) {
            if (null != ($token = ZMTokens::instance()->getTokenForHash($hash)) && $this->get('expireUrlToken')) {
                // expire after first use (set lifetime to 0)
                ZMTokens::instance()->updateToken($token, 0);
            }
            return $token;
        }

        // no token
        return null;
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
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');
        $session = $request->getSession();
        if ('GET' == $request->getMethod() && $session->isRegistered()) {
            if (!$this->cookieUpdated) {
                $this->onOptIn($request->getAccount(), array_key_exists(AUTO_LOGIN_COOKIE, $_COOKIE));
            }
        }
    }

    /**
     * Event handler for create account.
     */
    public function onCreateAccount($event) {
        $this->onOptIn($event->get('account'), $event->get('request')->getParameter(AUTO_LOGIN_OPT_IN));
    }

    /**
     * Event handler for login.
     */
    public function onLoginSuccess($event) {
        $this->onOptIn($event->get('account'), $event->get('request')->getParameter(AUTO_LOGIN_OPT_IN));
    }

    /**
     * Event handler for logout.
     */
    public function onLogoffSuccess($event) {
        if (array_key_exists(AUTO_LOGIN_COOKIE, $_COOKIE)) {
            setcookie(AUTO_LOGIN_COOKIE, 'expired', time() - 3600);
            $this->cookieUpdated = true;
        }
    }

}
