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

use zenmagick\base\Beans;
use zenmagick\base\Toolbox;
use zenmagick\base\Runtime;


/**
 * Allow Wordpress content to be displayed in ZenMagick.
 *
 * @package org.zenmagick.plugins.wordpress
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMWordpressPlugin extends Plugin {
    private $requestId_;
    private $requestHandler_;
    private $adapter_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Wordpress', 'Allows to display Wordpress content in ZenMagick', '${plugin.version}');
        $this->setContext('storefront');
        $this->requestId_ = '';
        $this->requestHandler_ = null;
        $this->adapter_ = null;
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('Wordpress Installation Folder', 'wordpressDir', '', 'Path to your Wordpress installation',
              'widget@ZMTextFormWidget#name=wordpressDir&default=&size=24&maxlength=255');
        $this->addConfigValue('Permalink Path Prefix', 'permaPrefix', '', 'Path prefix for Wordpress permalinks; leave empty if not using permalinks');
        $this->addConfigValue('WP enabled pages', 'wordpressEnabledPages', '', 'Comma separated list of pages that can display WP content (leave empty for all).');
        $this->addConfigValue('User syncing', 'syncUser', false, 'Automatically create WP account (and update)',
            'widget@ZMBooleanFormWidget#name=syncUser&default=false&label=Update WP');
        $this->addConfigValue('Nickname policy', 'requireNickname', true, 'Make nick name mandatory (empty nickname will skip automatic WP registration)',
            'widget@ZMBooleanFormWidget#name=requireNickname&default=true&label=Require nickname');
        $this->addConfigValue('URL rewriting', 'urlRewrite', true, 'Convert Wordpress URLs to store URLs pointing to the plugin templates',
            'widget@ZMBooleanFormWidget#name=urlRewrite&default=true&label=Rewrite WP URLs');
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled() {
        return parent::isEnabled() && !(defined('WP_USE_THEMES') && WP_USE_THEMES);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Handle init request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');
        $this->requestId_ = $request->getRequestId();

        // main define to get at things
        $wordpressDir = $this->get('wordpressDir');
        if (empty($wordpressDir)) {
            $wordpressDir = ZMSettings::get('plugins.wordpress.root');
        }
        define('ZM_WORDPRESS_ROOT', $wordpressDir);

        Runtime::getEventDispatcher()->listen($this);

        if ($this->get('requireNickname')) {
            // enable nick name field
            ZMSettings::set('isAccountNickname', true);
        }

        ZMUrlManager::instance()->setMapping('wp', array(
            'controller' => 'ZMWordpressController',
            'wp_index' => array('template' => 'views/wp/index.php'),
            'wp_single' => array('template' => 'views/wp/single.php'),
            'wp_page' => array('template' => 'views/wp/page.php'),
            'wp_archive' => array('template' => 'views/wp/archive.php'),
            'wp_archives' => array('template' => 'views/wp/archives.php'),
            'wp_search' => array('template' => 'views/wp/search.php')
        ));
    }

    /**
     * Get the WP bridge.
     */
    protected function getAdapter() {
        if (null == $this->adapter_) {
            $this->adapter_ = Beans::getBean('ZMWordpressAdapter');
        }

        return $this->adapter_;
    }

    /**
     * Handle view start.
     */
    public function onViewStart($event) {
        $request = $event->get('request');

        // create single request handler
        $wordpressEnabledPages = $this->get('wordpressEnabledPages');
        if ($this->initWP() && (empty($wordpressEnabledPages) || ZMLangUtils::inArray($request->getRequestId(), $wordpressEnabledPages))) {
            // need to do this on all enabled pages, not just wp
            $requestHandler = $this->getRequestHandler($request);
            if (Toolbox::asBoolean($this->get('urlRewrite'))) {
                $requestHandler->registerFilter($event->get('view'));
            }
        }
    }

    /**
     * Handle init done event.
     *
     * <p>Code in here can't be executed in <code>init()</code>, as it depends on the global
     * WP stuff being loaded first.</p>
     */
    public function onInitDone($event) {
        $request = $event->get('request');

        // create single request handler
        $wordpressEnabledPages = $this->get('wordpressEnabledPages');
        if ($this->initWP() && (empty($wordpressEnabledPages) || ZMLangUtils::inArray($request->getRequestId(), $wordpressEnabledPages))) {
            // need to do this on all enabled pages, not just wp
            $requestHandler = $this->getRequestHandler($request);
            $requestHandler->preProcess($request);

            // TODO: make optional
            if (false) {
                header($request->getProtocol().' 200 OK');
            }
        }

        if (Toolbox::asBoolean($this->get('syncUser'))) {
            // setup WP bridge hooks and additional validation rules
            if ('create_account' == $this->requestId_) {
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
                $this->container->get('validator')->addRules('registration', $rules);
            } else if ('account_password' == $this->requestId_) {
                // nothing
            } else if ('account_edit' == $this->requestId_) {
                $bridge = $this->getAdapter();
                $rules = array(
                    array("ZMWrapperRule", 'nickName', 'The entered nick name is already taken (wordpress).', array($bridge, 'vDuplicateNickname')),
                    array("ZMWrapperRule", 'email', 'The entered email address is already taken (wordpress).', array($bridge, 'vDuplicateChangedEmail'))
                );
                // optionally, make nick name required
                if ($this->get('requireNickname')) {
                    $rules[] = array('ZMRequiredRule', 'nickName', 'Please enter a nick name.');
                }
                $this->container->get('validator')->addRules('account', $rules);
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
        return !ZMLangUtils::isEmpty($this->get('permaPrefix'));
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobal($request) {
        $wordpressEnabledPages = $this->get('wordpressEnabledPages');
        if (empty($wordpressEnabledPages) || (!ZMLangUtils::isEmpty($request->getRequestId()) && ZMLangUtils::inArray($request->getRequestId(), $wordpressEnabledPages))) {
            if ($this->isPermalinksEnabled()) {
                $path = $request->getContext().$this->get('permaPrefix');
                if (false === strpos($_SERVER['REQUEST_URI'], '?')) {
                    // simulate empty query arg to make WP homepage work
                    $_SERVER['REQUEST_URI'] .= '?';
                }
                // make WP permalink parsing work
                $_SERVER['REQUEST_URI'] = str_replace($path, '', $_SERVER['REQUEST_URI']);
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
     * @param ZMRequest request The current request.
     * @return ZMWpRequestHandler The single request handler for this request.
     */
    public function getRequestHandler($request) {
        if (null == $this->requestHandler_) {
            $this->requestHandler_ = new ZMWordpressRequestHandler($this, $request);
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
            if (!ZMLangUtils::isEmpty($account->getNickName())) {
                $password = $event->get('clearPassword');
                if (!$this->getAdapter()->createAccount($account, $password)) {
                    $this->container->get('messageService')->info(_zm('Could not create wordpress account - please contact the store administrator.'));
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
            if (!ZMLangUtils::isEmpty($account->getNickName())) {
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
            if (null != $account && !ZMLangUtils::isEmpty($account->getNickName())) {
                $this->getAdapter()->updateAccount($account->getNickName(), null, $account->getEmail());
            }
        }
    }

}
