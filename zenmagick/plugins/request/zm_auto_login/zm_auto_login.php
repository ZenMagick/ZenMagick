<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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


define('ZM_AUTO_LOGIN_COOKIE', 'zm_auto_login');
define('ZM_AUTO_LOGIN_OPT_IN', 'autoLogin');


/**
 * Plugin to automtically login returning customers.
 *
 * @package org.zenmagick.plugins.zm_auto_login
 * @author DerManoMann
 * @version $Id$
 */
class zm_auto_login extends ZMPlugin {
    private $cookieUpdated;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Auto Login', 'Automatically login returning customers.', '${plugin.version}');
        $this->setLoaderSupport('ALL');
        $this->setScope(ZM_SCOPE_STORE);
        $this->cookieUpdated = false;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Opt In', 'optIn', 'true', 'Allow users to opt in', 'zen_cfg_select_option(array(\'true\',\'false\'),');
        $this->addConfigValue('Lifetime', 'lifetime', '7', 'Cookie/hash lifetime in days');
        $this->addConfigValue('Token support', 'useToken', 'false', 'Use the token service if installed', 'zen_cfg_select_option(array(\'true\',\'false\'),');
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        $this->zcoSubscribe();

        $session = ZMRequest::getSession();
        if ('GET' == ZMRequest::getMethod() && 'logoff' != ZMRequest::getPageName() && $session->isAnonymous()) {
            // try to login
            if (isset($_COOKIE[ZM_AUTO_LOGIN_COOKIE])) {
                // prepare cookie data
			          $cookie = explode('~~~', $_COOKIE[ZM_AUTO_LOGIN_COOKIE]);
                // need account to login
                $account = null;

                // validate using token or plain password hash in cookie
                if ($this->useTokenService()) {
                    // cookie contains token hash only
                    $token = ZMTokens::instance()->getTokenForHash($cookie[0]);
                    if (null != $token) {
                        // resource = auto_login/id/[accountId]
                        $bits = explode('/', $token->getResource());
                        if (3 == count($bits)) {
                            $account = ZMAccounts::instance()->getAccountForId((int)$bits[2]);
                            // renew cookie 
                        }
                    }
                } else {
                    // cookie contains accountId and password hash
                    if (null != ($account = ZMAccounts::instance()->getAccountForId($cookie[0]))) {
                        if ($cookie[1] != $account->getPassword()) {
                            // invalid password hash
                            $account = null;
                        }
                    }
                }

                if (null != $account) {
                    if ($session->registerAccount($account, $this)) {
                        ZMRequest::redirect(ZMToolbox::instance()->net->url(null, '', ZMRequest::isSecure()));
                    }
                }
            }
        }
    }


    /**
     * Check if we should be using the token service.
     *
     * @return boolean <code>true</code> if the token service should be used.
     */
    protected function useTokenService() {
        return ZMTools::asBoolean($this->get('useToken') && class_exists('ZMTokens'));
    }

    /**
     * Set auto login cookie depending on optIn.
     *
     * @param ZMAccount account The account.
     * @param string optIn The users optIn preference.
     */
    protected function onOptIn($account, $optIn) {
        if (!ZMTools::asBoolean($this->get('optIn')) || ZMTools::asBoolean($optIn)) {
            //TODO: use hash
            $cookie = implode('~~~', array($account->getId(), $account->getPassword()));
            setcookie(ZM_AUTO_LOGIN_COOKIE, $cookie, time()+60*60*24*$this->get('lifetime'));
            $this->cookieUpdated = true;
        }
    }

    /**
     * Event handler to update the cookie if required.
     *
     * @param array args Optional parameter.
     */
    public function onZMAllDone($args=array()) {
        $session = ZMRequest::getSession();
        if ('GET' == ZMRequest::getMethod() && $session->isRegistered()) {
            if (!$this->cookieUpdated) {
                $this->onOptIn(ZMRequest::getAccount(), array_key_exists(ZM_AUTO_LOGIN_COOKIE, $_COOKIE));
            }
        }
    }

    /**
     * Event handler for create account.
     *
     * @param array args Optional parameter.
     */
    public function onZMCreateAccount($args=array()) {
        $this->onOptIn($args['account'], ZMRequest::getParameter(ZM_AUTO_LOGIN_OPT_IN));
    }

    /**
     * Event handler for login.
     *
     * @param array args Optional parameter.
     */
    public function onZMLoginSuccess($args=array()) {
        $this->onOptIn($args['account'], ZMRequest::getParameter(ZM_AUTO_LOGIN_OPT_IN));
    }

    /**
     * Event handler for logout.
     *
     * @param array args Optional parameter.
     */
    public function onZMLogoffSuccess($args=array()) {
        if (array_key_exists(ZM_AUTO_LOGIN_COOKIE, $_COOKIE)) {
            setcookie(ZM_AUTO_LOGIN_COOKIE, 'expired', time() - 3600);
            $this->cookieUpdated = true;
        }
    }

}

?>
