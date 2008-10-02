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


/**
 * Plugin to enable phpBB support in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_phpbb2
 * @author mano
 * @version $Id$
 */
class zm_phpbb2 extends ZMPlugin {
    var $phpBBEnabled_;
    var $page_;
    // account before changes
    var $prePostAccount_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('phpBB', 'phpBB for ZenMagick');
        $this->setLoaderSupport('ALL');
        $this->phpBBEnabled_ = false;
        $this->page_ = '';
        $this->prePostAccount_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
    global $phpBB;

        parent::init();
    
        $this->phpBBEnabled_ = $phpBB->phpBB['installed'] == true;
        $this->page_ = ZMRequest::getPageName();
        $this->prePostAccount_ = ZMRequest::getAccount();

        if (!$this->phpBBEnabled_) {
            // nothing to do
            return;
        }

        // enable nickname field
        ZMSettings::set('isAccountNickname', true);

        if ('create_account' == $this->page_) {
            // add custom validation rules
            $rules = array(
                array('RequiredRule', 'nick', 'Please enter a nickname.'),
                array("WrapperRule", 'nick', 'The entered nickname is already taken (phpBB).', '_zmp_is_not_duplicate_nickname'),
                array("WrapperRule", 'email_address', 'The entered email address is already taken (phpBB).', '_zmp_is_not_duplicate_email')
            );
            ZMValidator::instance()->addRules('create_account', $rules);
            $this->zcoSubscribe();
        } else if ('account_password' == $this->page_) {
            $this->zcoSubscribe();
        } else if ('account_edit' == $this->page_) {
            $rules = array(
                array('RequiredRule', 'nick', 'Please enter a nickname.'),
                array("WrapperRule", 'nick', 'The entered nickname is already taken (phpBB).', '_zmp_is_not_duplicate_nickname'),
                array("WrapperRule", 'email_address', 'The entered email address is already taken (phpBB).', '_zmp_is_not_duplicate_email')
            );
            ZMValidator::instance()->addRules('edit_account', $rules);
            $this->zcoSubscribe();
        }
    }

    /**
     * Event callback for controller processing.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     *
     * @param array args Optional parameter ('view' => $view).
     */
    function onZMControllerProcessEnd($args) {
    global $phpBB;

        if ('POST' == ZMRequest::getMethod()) {
            $view = $args['view'];

            if ('create_account' == $this->page_ && 'success' == $view->getMappingId()) {
                // account created
                $email = ZMRequest::getParameter('email_address');
                $password = ZMRequest::getParameter('password');
                $nickName = ZMRequest::getParameter('nick');
                $phpBB->phpbb_create_account($nickName, $password, $email);
            }

            if ('account_password' == $this->page_ && 'success' == $view->getMappingId()) {
                $account = ZMRequest::getAccount();
                if (null != $account && !ZMTools::isEmpty($account->getNickName())) {
                    $newPassword = ZMRequest::getParameter('password_new');
                    $phpBB->phpbb_change_password($account->getNickName(), $newPassword);
                }
            }

            if ('account_edit' == $this->page_ && 'success' == $view->getMappingId()) {
                $account = $this->prePostAccount_;
                if (null != $account) {
                    $phpBB->phpbb_change_email($account->getEmail(), ZMRequest::getparameter('email_address'));
                }
            }
        }
    }

}


?>
