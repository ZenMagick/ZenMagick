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

define('ZM_SITE_SWITCH_MAX_SITES', 3);


/**
 * Plugin that allows to switch themes based on the hostname.
 *
 * @package org.zenmagick.plugins.zm_site_switch
 * @author mano
 * @version $Id$
 */
class zm_site_switch extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('Site Switch', 'Hostname based theme switching', '${plugin.version}');
        $this->setLoaderSupport('FOLDER');
    }

    /**
     * Default c'tor.
     */
    function zm_site_switch() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
    global $zm_request, $zm_runtime, $zm_server_names;

        parent::init();

        $this->addMenuItem('zm_site_switch', zm_l10n_get('Site Switching'), 'zm_site_switch_admin');

        $hostname = $zm_request->getHostname();

        if (isset($zm_server_names[$hostname])) {
            $zm_runtime->setThemeId($zm_server_names[$hostname]);
        }
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        zm_site_switch_remove_switcher();
    }

}

?>
