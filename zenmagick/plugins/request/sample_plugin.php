<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Sample plugin to illustrate a few key points of the ZenMagick plugin architecture.
 *
 * @package net.radebatz.zenmagick.plugins
 * @author mano
 * @version $Id$
 */
class sample_plugin extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function sample_plugin() {
        parent::__construct('ZenMagick Sample Plugin', 'This is the ZenMagick Sample Plugin');
        $this->setKeys(array('rq1key1', 'rq1key2'));
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->sample_plugin();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Plugin true/false', 'rq1key1', 'true', 'Select true or false', 'zen_cfg_select_option(array(\'true\',\'false\'),');
        $this->addConfigValue('Plugin text config', 'rq1key2', 'doh', 'Some text');
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        /*
         * this is the place to do init stuff other than just setting up the infrastructure
         */
      
        // set up as event subscriber
        $this->zcoSubscribe();

        // add admin page
        $this->addMenuItem('sample', zm_l10n_get('Sample Plugin Admin Page'), 'zm_sample_plugin_admin');
    }


    /**
     * As zco subscriber all methods that match a zen-cart zco event (see <code>ZMEvents</code> for more details)
     * will be called (back) automatically when subscribed...
     */
    function onNotifyHeaderStartIndex($args) {
        echo "Start of index page event callback in " . $this->getName() . " ...<br>";
    }

    /**
     * Create the plugin handler.
     *
     * <p>This is the method to be implemented by plugins that require a handler.</p>
     *
     * @return ZMPluginHandler A <code>ZMPluginHandler</code> instance or <code>null</code> if
     *  not supported.
     */
    function &createPluginHandler() {
    global $zm_request;

        return 'login' == $zm_request->getPageName() ? new sample_plugin_handler() : null;
    }

}


/**
 * Simple page filter.
 * @package net.radebatz.zenmagick.plugins
 */
class sample_plugin_handler extends ZMPluginHandler {

    /**
     * Default c'tor.
     */
    function sample_plugin_handler() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->sample_plugin_handler();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        $plugin = $this->getPlugin();
        return preg_replace('/<\/h1>/', ' (modified by ' . $plugin->getName() . ')</h1>', $contents, 1);
    }

}

/**
 * Sample admin page.
 *
 * @package net.radebatz.zenmagick.plugins
 */
function zm_sample_plugin_admin() {
    echo 'Sample Plugin Admin Page';
}

?>
