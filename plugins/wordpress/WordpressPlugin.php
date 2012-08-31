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
namespace ZenMagick\plugins\wordpress;

use ZenMagick\apps\store\plugins\Plugin;
use ZenMagick\base\Beans;
use ZenMagick\base\Toolbox;


/**
 * Allow Wordpress content to be displayed in ZenMagick.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class WordpressPlugin extends Plugin {
    private $requestHandler_ = null;
    private $adapter_ = null;

    /**
     * {@inheritDoc}
     */
    public function isEnabled() {
        return parent::isEnabled() && !(defined('WP_USE_THEMES') && WP_USE_THEMES);
    }

    /**
     * Prepare WP.
     */
    protected function prepareWP() {
        $settingsService = $this->container->get('settingsService');
        $settingsService->set('isAccountNickname', true);

        // main define to get at things
        $wordpressDir = $this->get('wordpressDir');
        if (empty($wordpressDir)) {
            $wordpressDir = $settingsService->get('plugins.wordpress.root');
        }
        define('ZM_WORDPRESS_ROOT', $wordpressDir);

        if ($this->get('requireNickname')) {
            // enable nick name field
            $settingsService->set('isAccountNickname', true);
        }
    }

    /**
     * Get the WP bridge.
     */
    protected function getAdapter() {
        if (null == $this->adapter_) {
            $this->adapter_ = Beans::getBean('ZenMagick\plugins\wordpress\WordpressAdapter');
        }

        return $this->adapter_;
    }

    /**
     * Handle view start.
     */
    public function onViewStart($event) {
        $request = $event->get('request');

        // create single request handler
        $wordpressEnabledPages = explode(',', $this->get('wordpressEnabledPages'));
        if ($this->initWP() && (empty($wordpressEnabledPages) || in_array($request->getRequestId(), $wordpressEnabledPages))) {
            // need to do this on all enabled pages, not just wp
            $requestHandler = $this->getRequestHandler($request);
            if (Toolbox::asBoolean($this->get('urlRewrite'))) {
                $requestHandler->registerFilter($event->get('view'));
            }
        }
    }

    /**
     * Handle init done event.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $requestId = $request->getRequestId();

        $this->prepareWP();

        // create single request handler
        $wordpressEnabledPages = explode(',', $this->get('wordpressEnabledPages'));
        if ($this->initWP() && (empty($wordpressEnabledPages) || in_array($request->getRequestId(), $wordpressEnabledPages))) {
            // need to do this on all enabled pages, not just wp
            $requestHandler = $this->getRequestHandler($request);
            $requestHandler->preProcess($request);

            // TODO: make optional
            if (false) {
                $protocol = $this->server->get('SERVER_PROTOCOL');
                header($protocol.' 200 OK');
            }
        }

        if (Toolbox::asBoolean($this->get('syncUser'))) {
            // setup WP bridge hooks and additional validation rules
            if ('create_account' == $requestId) {
                $bridge = $this->getAdapter();
                // add custom validation rules
                $rules = array(
                    array("ZMWrapperRule", 'nickName', 'The entered nick name is already taken (wordpress).', array($bridge, 'vDuplicateNickname')),
                    array("ZMWrapperRule", 'email', 'The entered email address is already taken (wordpress).', array($bridge, 'vDuplicateEmail'))
                );
                // optionally, make nick name required
                if ($this->get('requireNickname')) {
                    $rules[] = array('ZMRequiredRule', 'nickName', 'Please enter a nick name.');
                }
                $this->container->get('zmvalidator')->addRules('registration', $rules);
            } else if ('account_password' == $requestId) {
                // nothing
            } else if ('account_edit' == $requestId) {
                $bridge = $this->getAdapter();
                $rules = array(
                    array("ZMWrapperRule", 'nickName', 'The entered nick name is already taken (wordpress).', array($bridge, 'vDuplicateNickname')),
                    array("ZMWrapperRule", 'email', 'The entered email address is already taken (wordpress).', array($bridge, 'vDuplicateChangedEmail'))
                );
                // optionally, make nick name required
                if ($this->get('requireNickname')) {
                    $rules[] = array('ZMRequiredRule', 'nickName', 'Please enter a nick name.');
                }
                $this->container->get('zmvalidator')->addRules('account', $rules);
            }
        }
    }

    /**
     * Handle final content.
     */
    public function onFinaliseContent($event) {
        $request = $event->get('request');

        if ('' == $request->getRequestId()) {
            ob_start();
            wp_head();
            $wp_head = ob_get_clean();
            $content = $event->get('content');
            $content = preg_replace('/<\/head>/', $wp_head . '</head>', $content, 1);
            $event->set('content', $content);
        }
    }

    /**
     * Check if permalinks are configured.
     *
     * @return boolean <code>true</code> if permalink support is enabled, <code>false</code> if not.
     */
    public function isPermalinksEnabled() {
        return !Toolbox::isEmpty($this->get('permaPrefix'));
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobal($request) {
        $wordpressEnabledPages = explode(',', $this->get('wordpressEnabledPages'));
        if (empty($wordpressEnabledPages) || (!Toolbox::isEmpty($request->getRequestId()) && in_array($request->getRequestId(), $wordpressEnabledPages))) {
            if ($this->isPermalinksEnabled()) {
                $path = $request->getContext().$this->get('permaPrefix');
                // simulate empty query arg to make WP homepage work
                $requestUri = rtrim($request->getRequestUri(), '?').'?';
                // make WP permalink parsing work
                $requestUri = str_replace($path, '', $requestUri);
                $_SERVER['REQUEST_URI'] = $requestUri;
            }
            // load as proper global to make WP work - @#!!$&^ globals
            return array($this->getPluginDirectory().'/wp-include.php');
        }

        return parent::getGlobal($request);
    }

    /**
     * Execute WP index init.
     *
     * @param string query Parameter for WP <code>query_posts</code> as per WP docs.
     */
    public function query_posts($query='') {
    global $wpdb;

        if ($this->initWP() && !$this->isPermalinksEnabled()) {
            $wpdb->select(DB_NAME);
            query_posts($query);
        }
    }

    /**
     * Check if wp is available.
     *
     * @return boolean <code>true</code> if Wordpress is initialized.
     */
    public function initWP() {
    global $wpdb;

        return isset($wpdb);
    }

    /**
     * Get the request handler.
     *
     * @param ZenMagick\http\Request request The current request.
     * @return ZMWpRequestHandler The single request handler for this request.
     */
    public function getRequestHandler($request) {
        if (null == $this->requestHandler_) {
            $this->requestHandler_ = new WordpressRequestHandler($this, $request);
        }

        return $this->requestHandler_;
    }

    /**
     * Account created event callback.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     */
    public function onCreateAccount($event) {
        if (Toolbox::asBoolean($this->get('syncUser'))) {
            $account = $event->get('account');
            if (!Toolbox::isEmpty($account->getNickName())) {
                $password = $event->get('clearPassword');
                if (!$this->getAdapter()->createAccount($account, $password)) {
                    $event->get('request')->getSession()->getFlashBag()->info(_zm('Could not create wordpress account - please contact the store administrator.'));
                }
            }
        }
    }

    /**
     * Event callback for controller processing.
     *
     * <p>Here the additional processing is done by checking the result view id. As per convention,
     * ZenMagick controller will use the viewId 'success' if POST processing was successful.</p>
     */
    public function onPasswordChanged($event) {
        if (Toolbox::asBoolean($this->get('syncUser'))) {
            $account = $event->get('account');
            if (!Toolbox::isEmpty($account->getNickName())) {
                $password = $event->get('clearPassword');
                $this->getAdapter()->updateAccount($account->getNickName(), $password, $account->getEmail());
            }
        }
    }

    /**
     * Event callback for syncing users.
     */
    public function onAccountUpdated($event) {
        if (Toolbox::asBoolean($this->get('syncUser'))) {
            $request = $event->get('request');
            $account = $event->get('account');
            if (null != $account && !Toolbox::isEmpty($account->getNickName())) {
                $this->getAdapter()->updateAccount($account->getNickName(), null, $account->getEmail());
            }
        }
    }

}
