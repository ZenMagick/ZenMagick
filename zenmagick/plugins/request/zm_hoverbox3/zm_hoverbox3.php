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
 * Plugin to enable support for Hover Box3 in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_hoverbox3
 * @author mano
 * @version $Id$
 */
class zm_hoverbox3 extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Hover Box3', 'Hover Box3 support for ZenMagick', '${plugin.version}');
        $this->setLoaderSupport('ALL');
        $this->setScope(ZM_SCOPE_STORE);
    }

    /**
     * Create new instance.
     */
    function zm_hoverbox3() {
        $this->__construct();
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
    function install() {
        parent::install();
        zm_sql_patch(file($this->getPluginDir()."sql/use-sql-patch-tool-to-install.txt"), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        zm_sql_patch(file($this->getPluginDir()."sql/uninstall-HoverBox-sql.txt"), $this->messages_);
    }

    /**
     * Create the plugin handler.
     *
     * @return ZMPluginHandler A <code>ZMPluginHandler</code> instance or <code>null</code> if
     *  not supported.
     */
    function createPluginHandler() {
    global $zm_loader;

        return $zm_loader->create("H3PageContentHandler");
    }

}

?>
