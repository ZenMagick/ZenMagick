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

namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Plugin admin controller base class.
 *
 * @author DerManoMann
 */
class PluginAdminController extends DefaultController
{
    private $plugin;

    /**
     * Create a new instance.
     *
     * @param mixed plugin The parent plugin.
     */
    public function __construct($plugin)
    {
        parent::__construct();
        $this->plugin = $plugin;
    }

    /**
     * Set the plugin.
     *
     * @param mixed plugin A <code>ZMPlugin</code> instance or plugin id.
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getPlugin()
    {
        if (!is_object($this->plugin)) {
            $this->plugin = $this->container->get('pluginService')->getPluginForId($this->plugin);
        }

        return $this->plugin;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request)
    {
        return array('plugin' => $this->getPlugin());
    }

}
