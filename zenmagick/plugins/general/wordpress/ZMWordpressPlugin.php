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

define('FILENAME_WP', 'wp');

/**
 * Allow Wordpress content to be displayed in ZenMagick.
 *
 * @package org.zenmagick.plugins.wordpress
 * @author DerManoMann
 * @version $Id$
 */
class ZMWordpressPlugin extends Plugin implements ZMRequestHandler {
    private $requestId_;
    private $requestHandler_;
    private $adapter_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Wordpress', 'Allows to display Wordpress content in ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->requestId_ = '';
        $this->requestHandler_ = null;
        $this->adapter_ = null;
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

        $this->addConfigValue('Wordpress Installation Folder', 'wordpressDir', '', 'Path to your Wordpress installation',
              'widget@TextFormWidget#name=wordpressDir&default=&size=24&maxlength=255');
        $this->addConfigValue('Permalink Path Prefix', 'permaPrefix', '', 'Path prefix for Wordpress permalinks; leave empty if not using permalinks');
        $this->addConfigValue('WP enabled pages', 'wordpressEnabledPages', FILENAME_WP, 'Comma separated list of pages that can display WP content (leave empty for all).');
        $this->addConfigValue('User syncing', 'syncUser', false, 'Automatically create WP account (and update)', 
            'widget@BooleanFormWidget#name=syncUser&default=false&label=Update WP');
        $this->addConfigValue('Nickname policy', 'requireNickname', true, 'Make nick name mandatory (empty nickname will skip automatic WP registration)', 
            'widget@BooleanFormWidget#name=requireNickname&default=true&label=Require nickname');
        $this->addConfigValue('URL rewriting', 'urlRewrite', true, 'Convert Wordpress URLs to store URLs pointing to the plugin templates', 
            'widget@BooleanFormWidget#name=urlRewrite&default=true&label=Rewrite WP URLs');
    }

    /**
     * {@inheritDoc}
     */
    public function initRequest($request) {
        $this->requestId_ = $request->getRequestId();

        // main define to get at things
        $wordpressDir = $this->get('wordpressDir');
        if (empty($wordpressDir)) {
            $wordpressDir = ZMSettings::get('plugins.wordpress.root');
        }
        define('ZM_WORDPRESS_ROOT', $wordpressDir);

        ZMEvents::instance()->attach($this);

        if ($this->get('requireNickname')) {
            // enable nick name field
            ZMSettings::set('isAccountNickname', true);
        }

        ZMUrlManager::instance()->setMapping(FILENAME_WP, array(
            'controller' => 'WordpressController',
            FILENAME_WP.'_index' => array('template' => FILENAME_WP.'/index'),
            FILENAME_WP.'_single' => array('template' => FILENAME_WP.'/single'),
            FILENAME_WP.'_page' => array('template' => FILENAME_WP.'/page'),
            FILENAME_WP.'_archive' => array('template' => FILENAME_WP.'/archive'),
            FILENAME_WP.'_archives' => array('template' => FILENAME_WP.'/archives'),
            FILENAME_WP.'_search' => array('template' => FILENAME_WP.'/search')
        ));
    }

    /**
     * Get the WP bridge.
     */
    protected function getAdapter() {
        if (null == $this->adapter_) {
            $this->adapter_ = ZMLoader::make('WordpressAdapter');
        }

        return $this->adapter_;
    }

    /**
     * Handle init done event.
     *
     * <p>Code in here can't be executed in <code>init()</code>, as it depends on the global
     * WP stuff being loaded first.</p>
     *
     * @param array args Optional event args.
     */
    public function onZMInitDone($args=null) {
        $request = $args['request'];

        // create single request handler
        $wordpressEnabledPages = $this->get('wordpressEnabledPages');
        if ($this->initWP() && (empty($wordpressEnabledPages) || ZMLangUtils::inArray($request->getRequestId(), $wordpressEnabledPages))) {
            // need to do this on all enabled pages, not just wp
            $requestHandler = $this->getRequestHandler($request);
            $requestHandler->preProcess($request);
            if (ZMLangUtils::asBoolean($this->get('urlRewrite'))) {
                $requestHandler->register();
            }

            // TODO: make optional
            if (false) {
                header($request->getProtocol().' 200 OK');
            }
        }

        if (ZMLangUtils::asBoolean($this->get('syncUser'))) {
            // setup WP bridge hooks and additional validation rules
            if ('create_account' == $this->requestId_) {
                $bridge = $this->getAdapter();
                // add custom validation rules
                $rules = array(
                    array("WrapperRule", 'nickName', 'The entered nick name is already taken (wordpress).', array($bridge, 'vDuplicateNickname')),
                    array("WrapperRule", 'email', 'The entered email address is already taken (wordpress).', array($bridge, 'vDuplicateEmail'))
                );
                // optionally, make nick name required
                if ($this->get('requireNickname')) {
                    $rules[] = array('RequiredRule', 'nickName', 'Please enter a nick name.');
                }
                ZMValidator::instance()->addRules('registration', $rules);
            } else if ('account_password' == $this->requestId_) {
                // nothing
            } else if ('account_edit' == $this->requestId_) {
                $bridge = $this->getAdapter();
                $rules = array(
                    array("WrapperRule", 'nickName', 'The entered nick name is already taken (wordpress).', array($bridge, 'vDuplicateNickname')),
                    array("WrapperRule", 'email', 'The entered email address is already taken (wordpress).', array($bridge, 'vDuplicateChangedEmail'))
                );
                // optionally, make nick name required
                if ($this->get('requireNickname')) {
                    $rules[] = array('RequiredRule', 'nickName', 'Please enter a nick name.');
                }
                ZMValidator::instance()->addRules('account', $rules);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $request = $args['request'];
        $contents = $args['contents'];

        if (FILENAME_WP == $request->getRequestId()) {
            ob_start();
            wp_head();
            $wp_head = ob_get_clean();
            $args['contents'] = preg_replace('/<\/head>/', $wp_head . '</head>', $contents, 1);
        }

        return $args;
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
        if (empty($wordpressEnabledPages) || ZMLangUtils::inArray($request->getRequestId(), $wordpressEnabledPages)) {
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
            return array($this->getPluginDirectory().'wp-include.gphp');
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
            $this->requestHandler_ = ZMLoader::make('WordpressRequestHandler', $this, $request);
        }

        return $this->requestHandler_;
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
        if (ZMLangUtils::asBoolean($this->get('syncUser'))) {
            $account = $args['account'];
            if (!ZMLangUtils::isEmpty($account->getNickName())) {
                $password = $args['clearPassword'];
                if (!$this->getAdapter()->createAccount($account, $password)) {
                    ZMMessages::instance()->info(_zm('Could not create wordpress account - please contact the store administrator.'));
                }
            }
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
        if (ZMLangUtils::asBoolean($this->get('syncUser'))) {
            $account = $args['account'];
            if (!ZMLangUtils::isEmpty($account->getNickName())) {
                $password = $args['clearPassword'];
                $this->getAdapter()->updateAccount($account->getNickName(), $password, $account->getEmail());
            }
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
    public function onZMControllerProcessEnd($args) {
        $request = $args['request'];

        if (ZMLangUtils::asBoolean($this->get('syncUser'))) {
            if ('POST' == $request->getMethod()) {
                $view = $args['view'];
                if ('account_edit' == $this->requestId_ && 'success' == $view->getMappingId()) {
                    $account = ZMAccounts::instance()->getAccountForId($request->getAccountId());
                    if (null != $account && !ZMLangUtils::isEmpty($account->getNickName())) {
                        $this->getAdapter()->updateAccount($account->getNickName(), null, $account->getEmail());
                    }
                }
            }
        }
    }

}
