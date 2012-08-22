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
namespace zenmagick\apps\store\plugins;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMException;
use zenmagick\http\plugins\HttpPlugin;

use zenmagick\apps\store\menu\MenuElement;

/**
 * Store plugin base class.
 *
 * <p>Plugins are <strong>NOT</strong> compatible with zen-cart modules.</p>
 *
 * <p>The plugin code (id) is based on the plugin class/file name.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Plugin extends HttpPlugin {
    private $messages_ = array();

    /**
     * Get optional installation messages.
     *
     * @return array List of <code>zenmagick\http\messages\Message</code> instances.
     */
    public function getMessages() {
        return $this->messages_;
    }

    /**
     * Install this plugin.
     *
     * <p>This default implementation will check for a <code>sql/install.sql</code> script and run it if found.</p>
     */
    public function install() {
        $file = $this->getPluginDirectory()."/sql/install.sql";
        if (file_exists($file)) {
            $this->executePatch(file($file), $this->messages_);
        }
    }

    /**
     * Remove this plugin.
     *
     * <p>This default implementation will check for a <code>sql/uninstall.sql</code> script and run it if found.</p>
     */
    public function remove() {
        $file = $this->getPluginDirectory()."/sql/uninstall.sql";
        if (file_exists($file)) {
            $this->executePatch(file($file), $this->messages_);
        }
    }

    /**
     * Get the preferred sort order (if any).
     *
     * @return int The sort order value.
     */
    public function getPreferredSortOrder() {
        return (int) $this->getMeta('preferredSortOrder');
    }

    /**
     * Execute a SQL patch.
     *
     * @param string sql The sql.
     * @param array Result message list.
     * @param boolean Debug flag.
     * @return boolean <code>true</code> for success, <code>false</code> if the execution fails.
     */
    public function executePatch($sql, $messages, $debug=false) {
        if (!empty($sql)) {
            $results = \zenmagick\apps\admin\utils\SQLRunner::execute_sql($sql, $debug);
            foreach (\zenmagick\apps\admin\utils\SQLRunner::process_patch_results($results) as $msg) {
                $messages[] = $msg;
            }
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Init this plugin.
     *
     * <p>This method is part of the lifecylce of a plugin during storefront request handling.</p>
     * <p>Code to set up internal resources should be placed here, rather than in the * constructor.</p>
     *
     * @deprecated Use event callbacks instead.
     */
    public function init() {
    }

    /**
     * Check if the plugin is installed.
     *
     * @return boolean <code>true</code> if the plugin is installed, <code>false</code> if not.
     */
    public function isInstalled() {
        return (boolean) $this->getMeta('installed');
    }

    /**
     * Resolve a plugin relative URI.
     *
     * <p>The given <code>uri</code> is assumed to be relative to the plugin folder.</p>
     *
     * @param string uri The relative URI.
     * @return string An absolute URI or <code>null</code>.
     */
    public function pluginURL($uri) {
        if (null == $this->getPluginDirectory()) {
            throw new ZMException('pluginDirectory missing');
        }

        $path = $this->getPluginDirectory().'/'.$uri;
        $templateView = $this->container->get('defaultView');
        return $templateView->getResourceManager()->file2uri($path);
    }

}
