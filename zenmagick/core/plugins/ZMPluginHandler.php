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
 * Plugin handler.
 *
 * <p>Base class to implement plugin behaviour.</p>
 *
 * <p>Plugin handler are the work horse in the plugin architecture. The plugin
 * class itself is just the glue between the runtime and the handler.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins
 * @version $Id$
 */
class ZMPluginHandler extends ZMObject {
    var $plugin_;

    /**
     * Create new instance.
     */
    function ZMPluginHandler() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMPluginHandler();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set plugin.
     *
     * @param ZMPlugin plugin The associated plugin.
     */
    function setPlugin($plugin) {
        $this->plugin_ = $plugin;
    }

    /**
     * Get plugin.
     *
     * @return ZMPlugin The associated plugin.
     */
    function getPlugin() {
        return $this->plugin_;
    }

    /**
     * Register this plugin as zen-cart zco subscriber.
     */
    function zcoSubscribe() {
        ZMEvents::instance()->attach($this);
    }

    /**
     * Un-register this plugin as zen-cart zco subscriber.
     */
    function zcoUnsubscribe() {
        ZMEvents::instance()->detach($this);
    }

    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        return $contents;
    }

}

?>
