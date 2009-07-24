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
 * Plugin to enable phpBB2 support in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_phpbb2
 * @author mano
 * @version $Id$
 */
class zm_phpbb2 extends Plugin {
    var $phpBBEnabled_;
    var $page_;
    // account before changes
    var $prePostAccount_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('phpBB2', 'phpBB2 for ZenMagick');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
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
        $this->page_ = ZMRequest::instance()->getRequestId();
        $this->prePostAccount_ = ZMRequest::instance()->getAccount();

        if (!$this->phpBBEnabled_) {
            // nothing to do
            return;
        }

        // enable nickname field
        ZMSettings::set('isAccountNickname', true);
    }

    /**
     * Init done callback.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     *
     * @param array args Optional parameter.
     */
    public function onZMInitDone($args=null) {
        if ('create_account' == $this->page_) {
            // add custom validation rules
            $rules = array(
                array('RequiredRule', 'nickName', 'Please enter a nickname.'),
                array("WrapperRule", 'nickName', 'The entered nickname is already taken (phpBB).', '_zmp_is_not_duplicate_nickname'),
                array("WrapperRule", 'email', 'The entered email address is already taken (phpBB).', '_zmp_is_not_duplicate_email')
            );
            ZMValidator::instance()->addRules('create_account', $rules);
            ZMEvents::instance()->attach($this);
        } else if ('account_password' == $this->page_) {
            ZMEvents::instance()->attach($this);
        } else if ('account_edit' == $this->page_) {
            $rules = array(
                array('RequiredRule', 'nickName', 'Please enter a nickname.'),
                array("WrapperRule", 'nickName', 'The entered nickname is already taken (phpBB).', '_zmp_is_not_duplicate_nickname'),
                array("WrapperRule", 'email', 'The entered email address is already taken (phpBB).', '_zmp_is_not_duplicate_email')
            );
            ZMValidator::instance()->addRules('edit_account', $rules);
            ZMEvents::instance()->attach($this);
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

        if ('POST' == ZMRequest::instance()->getMethod()) {
            $view = $args['view'];

            if ('create_account' == $this->page_ && 'success' == $view->getMappingId()) {
                // account created
                $email = ZMRequest::instance()->getParameter('email');
                $password = ZMRequest::instance()->getParameter('password');
                $nickName = ZMRequest::instance()->getParameter('nickName');
                $phpBB->phpbb_create_account($nickName, $password, $email);
            }

            if ('account_password' == $this->page_ && 'success' == $view->getMappingId()) {
                $account = ZMRequest::instance()->getAccount();
                if (null != $account && !ZMLangUtils::isEmpty($account->getNickName())) {
                    $newPassword = ZMRequest::instance()->getParameter('password_new');
                    $phpBB->phpbb_change_password($account->getNickName(), $newPassword);
                }
            }

            if ('account_edit' == $this->page_ && 'success' == $view->getMappingId()) {
                $account = $this->prePostAccount_;
                if (null != $account) {
                    $phpBB->phpbb_change_email($account->getEmail(), ZMRequest::instance()->getParameter('email'));
                }
            }
        }
    }

}


?>
