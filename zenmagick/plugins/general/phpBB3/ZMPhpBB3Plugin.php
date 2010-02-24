<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package org.zenmagick.plugins.phpbb3
 * @author DerManoMann
 * @version $Id$
 */
class ZMPhpBB3Plugin extends Plugin implements ZMRequestHandler {
    private $page_;
    private $prePostAccount_;
    private $adapter_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('phpBB3', 'phpBB3 for ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->page_ = '';
        $this->prePostAccount_ = null;
        $this->adapter_ = null;
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

        $this->addConfigValue('phpBB3 Installation Folder', 'phpBB3Dir', '', 'Path to your phpBB3 installation',
              'widget@TextFormWidget#name=phpBB3Dir&default=&size=24&maxlength=255');
        $this->addConfigValue('Nickname policy', 'requireNickname', true, 'Make nickname mandatory (If disabled, automatic phpBB registration will be skipped)', 
            'widget@BooleanFormWidget#name=requireNickname&default=true&label=Require nickname');
    }


    /**
     * Get the phpBB3 adapter.
     */
    protected function getAdapter() {
        if (null == $this->adapter_) {
            $this->adapter_ = new ZMPhpBB3Adapter();
        }

        return $this->adapter_;
    }

    /**
     * {@inheritDoc}
     */
    public function initRequest($request) {
        parent::init();
        $this->page_ = ZMRequest::instance()->getRequestId();
        $this->prePostAccount_ = ZMRequest::instance()->getAccount();

        // main define to get at things
        $phpBB3Dir = $this->get('phpBB3Dir');
        if (empty($phpBB3Dir)) {
            $default = defined('DIR_WS_PHPBB') ? DIR_WS_PHPBB : null;
            $phpBB3Dir = ZMSettings::get('plugins.pbpBB3.root', $default);
        }
        define('ZM_PHPBB3_ROOT', $phpBB3Dir);

        // enable nick name field
        ZMSettings::set('isAccountNickname', true);

        // using events
        ZMEvents::instance()->attach($this);

        // register tests
        ZMLoader::instance()->addPath($this->getPluginDirectory().'tests/');
        ZMSettings::append('plugins.unitTests.tests.custom', 'TestZMPhpBB3Adapter');
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
            if (ZMLangUtils::asBoolean($this->get('requireNickname'))) {
                $rules[] = array('RequiredRule', 'nickName', 'Please enter a nick name.');
            }
            ZMValidator::instance()->addRules('registration', $rules);
        } else if ('account_password' == $this->page_) {
            ZMEvents::instance()->attach($this);
        } else if ('account_edit' == $this->page_) {
            $phpBB = $this->getAdapter();
            $rules = array(
                array("WrapperRule", 'nickName', 'The entered nick name is already taken (phpBB3).', array($phpBB, 'vDuplicateNickname')),
                array("WrapperRule", 'email', 'The entered email address is already taken (phpBB3).', array($phpBB, 'vDuplicateChangedEmail'))
            );
            // optionally, make nick name required
            if (ZMLangUtils::asBoolean($this->get('requireNickname'))) {
                $rules[] = array('RequiredRule', 'nickName', 'Please enter a nick name.');
            }
            ZMValidator::instance()->addRules('account', $rules);
            ZMEvents::instance()->attach($this);
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
        $request = $args['request'];

        if ('POST' == $request->getMethod()) {
            $view = $args['view'];
            if ('account_edit' == $this->page_ && 'success' == $view->getMappingId()) {
                $account = ZMAccounts::instance()->getAccountForId($request->getAccountId());
                if (null != $account && !ZMLangUtils::isEmpty($account->getNickName())) {
                    $this->getAdapter()->updateAccount($account->getNickName(), null, $account->getEmail());
                }
            }
        }
    }

}


?>
