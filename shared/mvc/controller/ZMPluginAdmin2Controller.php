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


/**
 * Plugin admin controller base class.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.plugins
 */
class ZMPluginAdmin2Controller extends ZMController {
    private $plugin_;


    /**
     * Create a new instance.
     *
     * @param mixed plugin The parent plugin.
     */
    public function __construct($plugin) {
        parent::__construct();
        $this->plugin_ = $plugin;
    }


    /**
     * Set the plugin.
     *
     * @param mixed plugin A <code>ZMPlugin</code> instance or plugin id.
     */
    public function setPlugin($plugin) {
        $this->plugin_ = $plugin;
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getPlugin() {
        if (!is_object($this->plugin_)) {
            $this->plugin_ = $this->container->get('pluginService')->getPluginForId($this->plugin_);
        }

        return $this->plugin_;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        return array('plugin' => $this->getPlugin());
    }

}
