<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\plugins\phpbb3;

use zenmagick\apps\store\plugins\Plugin;
use zenmagick\base\Toolbox;
use zenmagick\base\Runtime;

/**
 * Plugin to enable phpBB3 support in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PhpBB3Plugin extends Plugin {
    private $page_ = '';
    private $prePostAccount_ = null;
    private $adapter_ = null;


    /**
     * Get the phpBB3 adapter.
     */
    protected function getAdapter() {
        if (null == $this->adapter_) {
            $this->adapter_ = new PhpBB3Adapter();
        }

        return $this->adapter_;
    }

    /**
     * Init code that requires the request.
     *
     * <p>Setup additional validation rules; this is done here to avoid getting in the way of
     * custom global/theme validation rule setups.</p>
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $this->page_ = $request->getRequestId();
        $this->prePostAccount_ = $request->getAccount();

        $settingsService = $this->container->get('settingsService');

        // main define to get at things
        $phpBB3Dir = $this->get('phpBB3Dir');
        if (empty($phpBB3Dir)) {
            $phpBB3Dir = $settingsService->get('plugins.pbpBB3.root');
        }
        define('ZM_PHPBB3_ROOT', $phpBB3Dir);

        // enable nick name field
        $settingsService->set('isAccountNickname', true);

        if ('create_account' == $this->page_) {
            $phpBB = $this->getAdapter();
            // add custom validation rules
            $rules = array(
                array("ZMWrapperRule", 'nickName', 'The entered nick name is already taken (phpBB3).', array($phpBB, 'vDuplicateNickname')),
                array("ZMWrapperRule", 'email', 'The entered email address is already taken (phpBB3).', array($phpBB, 'vDuplicateEmail'))
            );
            // optionally, make nick name required
            if (Toolbox::asBoolean($this->get('requireNickname'))) {
                $rules[] = array('ZMRequiredRule', 'nickName', 'Please enter a nick name.');
            }
            $this->container->get('zmvalidator')->addRules('registration', $rules);
        } else if ('account_password' == $this->page_) {
            // ??
        } else if ('account_edit' == $this->page_) {
            $phpBB = $this->getAdapter();
            $rules = array(
                array("ZMWrapperRule", 'nickName', 'The entered nick name is already taken (phpBB3).', array($phpBB, 'vDuplicateNickname')),
                array("ZMWrapperRule", 'email', 'The entered email address is already taken (phpBB3).', array($phpBB, 'vDuplicateChangedEmail'))
            );
            // optionally, make nick name required
            if (Toolbox::asBoolean($this->get('requireNickname'))) {
                $rules[] = array('ZMRequiredRule', 'nickName', 'Please enter a nick name.');
            }
            $this->container->get('zmvalidator')->addRules('account', $rules);
        }
    }

    /**
     * Account created event callback.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     */
    public function onCreateAccount($event) {
        $account = $event->get('account');
        if (!Toolbox::isEmpty($account->getNickName())) {
            $password = $event->get('clearPassword');
            $this->getAdapter()->createAccount($account, $password);
        }
    }

    /**
     * Event callback for controller processing.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     */
    public function onPasswordChanged($event) {
        $account = $event->get('account');
        if (!Toolbox::isEmpty($account->getNickName())) {
            $password = $event->get('clearPassword');
            $this->getAdapter()->updateAccount($account->getNickName(), $password, $account->getEmail());
        }
    }

    /**
     * Event callback for syncing users.
     */
    public function onAccountUpdated($event) {
        $account = $event->get('account');
        if (null != $account && !Toolbox::isEmpty($account->getNickName())) {
            $this->getAdapter()->updateAccount($account->getNickName(), null, $account->getEmail());
        }
    }

}
