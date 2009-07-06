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
 * Plugin to enable phpBB3 support in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_phpbb3
 * @author DerManoMann
 * @version $Id$
 */
class zm_phpbb3 extends Plugin {
    private $page_;
    private $prePostAccount_;
    private $phpBB3_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('phpBB3', 'phpBB3 for ZenMagick');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->page_ = '';
        $this->prePostAccount_ = null;
        $this->phpBB3_ = null;
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
    public function install() {
        parent::install();

        $this->addConfigValue('phpBB3 Installation Folder', 'phpBB3Dir', '', 'Path to your phpBB3 installation');
        // warning: screwed logic!
        $this->addConfigValue('Nickname policy', 'requireNickname', true, 'Leave nickname as optional (will skip automatic phpBB registration)', 
            "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>'No'), array('id'=>'0', 'text'=>'Yes')), ");
    }


    /**
     * Get the phpBB3 adapter.
     */
    protected function getAdapter() {
        if (null == $this->phpBB3_) {
            $this->phpBB3_ = new ZMPhpBB3();
        }

        return $this->phpBB3_;
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();
        $this->page_ = ZMRequest::getRequestId();
        $this->prePostAccount_ = ZMRequest::getAccount();

        // main define to get at things
        $phpBB3Dir = $this->get('phpBB3Dir');
        if (empty($phpBB3Dir)) {
            $phpBB3Dir = ZMSettings::get('plugins.zm_pbpbb3.root', DIR_WS_PHPBB);
        }
        define('ZM_PHPBB3_ROOT', $phpBB3Dir);

        // enable nick name field
        ZMSettings::set('isAccountNickname', true);

        // using events
        $this->zcoSubscribe();

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing ZMTestCase
            ZMLoader::instance()->addPath($this->getPluginDirectory().'tests/');
            $tests->addTest('TestZMPhpBB3');
        }
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
            $phpBB = $this->getAdapter();
            // add custom validation rules
            $rules = array(
                array("WrapperRule", 'nickName', 'The entered nick name is already taken (phpBB3).', array($phpBB, 'vDuplicateNickname')),
                array("WrapperRule", 'email', 'The entered email address is already taken (phpBB3).', array($phpBB, 'vDuplicateEmail'))
            );
            // optionally, make nick name required
            if ($this->get('requireNickname')) {
                $rules[] = array('RequiredRule', 'nickName', 'Please enter a nick name.');
            }
            ZMValidator::instance()->addRules('registration', $rules);
        } else if ('account_password' == $this->page_) {
            $this->zcoSubscribe();
        } else if ('account_edit' == $this->page_) {
            $phpBB = $this->getAdapter();
            $rules = array(
                array("WrapperRule", 'nickName', 'The entered nick name is already taken (phpBB3).', array($phpBB, 'vDuplicateNickname')),
                array("WrapperRule", 'email', 'The entered email address is already taken (phpBB3).', array($phpBB, 'vDuplicateChangedEmail'))
            );
            // optionally, make nick name required
            if ($this->get('requireNickname')) {
                $rules[] = array('RequiredRule', 'nickName', 'Please enter a nick name.');
            }
            ZMValidator::instance()->addRules('account', $rules);
            $this->zcoSubscribe();
        }
    }

    /**
     * Account created event callback.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     *
     * @param array args Optional parameter.
     */
    public function onZMCreateAccount($args) {
        $account = $args['account'];
        if (!ZMLangUtils::isEmpty($account->getNickName())) {
            $password = $args['clearPassword'];
            $this->getAdapter()->createAccount($account, $password);
        }
    }

    /**
     * Event callback for controller processing.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     *
     * @param array args Optional parameter.
     */
    public function onZMPasswordChanged($args) {
        $account = $args['account'];
        if (!ZMLangUtils::isEmpty($account->getNickName())) {
            $password = $args['clearPassword'];
            $this->getAdapter()->updateAccount($account->getNickName(), $password, $account->getEmail());
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
        if ('POST' == ZMRequest::getMethod()) {
            $view = $args['view'];
            if ('account_edit' == $this->page_ && 'success' == $view->getMappingId()) {
                $account = ZMAccounts::instance()->getAccountForId(ZMRequest::getAccountId());
                if (null != $account && !ZMLangUtils::isEmpty($account->getNickName())) {
                    $this->getAdapter()->updateAccount($account->getNickName(), null, $account->getEmail());
                }
            }
        }
    }

}


?>
