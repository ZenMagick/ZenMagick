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

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Auto Login', 'Automatically login returning customers.', '${plugin.version}');
        $this->setLoaderSupport('ALL');
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
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        if ($this->isEnabled()) {
            $this->zcoSubscribe();
        }

        $session = ZMRequest::getSession();
        if ('GET' == ZMRequest::getMethod() && 'logoff' != ZMRequest::getPageName() && $session->isAnonymous() && $this->isEnabled()) {
            // try to login
            if (isset($_COOKIE[ZM_AUTO_LOGIN_COOKIE])) {
			          $cookie = explode('~~~', $_COOKIE[ZM_AUTO_LOGIN_COOKIE]);
                if (null != ($account = ZMAccounts::instance()->getAccountForId($cookie[0]))) {
                    // TODO: use hash
                    if ($cookie[1] == $account->getPassword()) {
                        if (ZM_ACCOUNT_AUTHORIZATION_BLOCKED == $account->getAuthorization()) {
                            ZMMessages::instance()->error(zm_l10n_get('Access denied.'));
                            return;
                        }

                        // info only
                        ZMEvents::instance()->fireEvent($this, ZM_EVENT_LOGIN_SUCCESS, array('controller' => $this, 'account' => $account));

                        // update session with valid account
                        $session->recreate();
                        $session->setAccount($account);

                        // update login stats
                        ZMAccounts::instance()->updateAccountLoginStats($account->getId());

                        // restore cart contents
                        $session->restoreCart();

                        ZMRequest::redirect(ZMToolbox::instance()->net->url(null));
                    }
                }
            }
        }
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
        }
    }

}

?>
