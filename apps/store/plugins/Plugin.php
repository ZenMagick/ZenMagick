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
    private $messages_ = null;


    /**
     * Create new plugin instance.
     *
     * @param array config The plugin configuration.
     */
    public function __construct(array $config) {
        parent::__construct($config);
        $this->messages_ = array();
    }


    /**
     * Get optional installation messages.
     *
     * @return array List of <code>zenmagick\http\messages\Message</code> instances.
     */
    public function getMessages() {
        return $this->messages_;
    }

    /**
     * Get optional files to be loaded in global scope.
     *
     * <p>Files returned here would typically have an extension different to <em>.php</em> as otherwise
     * the loader will load them as static.</p>
     *
     * @param zenmagick\http\Request request The current request.
     * @return array List of filenames relative to the plugin location.
     */
    public function getGlobal($request) {
        return array();
    }

    /**
     * Install this plugin.
     */
    public function install() {
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
            $results = zenmagick\apps\admin\utils\SQLRunner::execute_sql($sql, $debug);
            foreach (zenmagick\apps\admin\utils\SQLRunner::process_patch_results($results) as $msg) {
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
     * Add new plugin menu group.
     *
     * @param string title The page title.
     * @param string parentid Optional parent id; default is <em>plugins</em>.
     * @return string The menu key to be used to add items to this group.
     * @todo: fix and allow optional other parameter, etc...
     */
    public function addMenuGroup($title, $parentId='configuration') {
        if ($this->container->has('adminMenu')) {
            $adminMenu = $this->container->get('adminMenu');
            if (null != ($parent = $adminMenu->getElement($parentId))) {
                $id = $parentId.'-'.$this->getId().microtime();
                $item = new MenuElement($id, $title);
                $parent->addChild($item);
                return $id;
            }
        }
        return null;
    }

    /**
     * Add custom plugin admin page to admin navigation.
     *
     * <p>Plugins are expected to implement a corresponding controller for the configured reuqestId.</p>
     *
     * @param string title The page title.
     * @param string requestId The corresponding requestId.
     * @param string menuKey Optional key determining where the menu item should appear; default is <em>'configuration-plugins'</em>.
     */
    public function addMenuItem($title, $requestId, $menuKey='configuration-plugins') {
        if ($this->container->has('adminMenu')) {
            $adminMenu = $this->container->get('adminMenu');
            if (null != ($parent = $adminMenu->getElement($menuKey))) {
                $item = new MenuElement($menuKey.'-'.$requestId, $title);
                $item->setRequestId($requestId);
                $parent->addChild($item);
            }
        }
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
