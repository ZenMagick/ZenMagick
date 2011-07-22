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


/**
 * Admin controller for plugins.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMPluginsController extends ZMController {
    private static $TYPE_MAP = array(
        'order_total' => 'ZMOrderTotal',
        'payment' => 'ZMPaymentType'
    );

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
    public function getViewData($request) {
        // TODO: make flat list
        $pluginList['general'] = ZMPlugins::instance()->getAllPlugins(0, false);
        return array('pluginList' => $pluginList);
    }

    /**
     * Get the plugin type.
     *
     * @param ZMPlugin plugin The plugin.
     * @return String The type.
     */
    protected function getPluginType($plugin) {
        $types = array();
        foreach (self::$TYPE_MAP as $name => $type) {
            if ($plugin instanceof $type) {
                $types[] = $name;
            }
        }

        if (empty($types)) {
            $types[] = 'general';
        }

        return implode(',', $types);
    }

    /**
     * Refresh plugin status data.
     */
    protected function refreshPluginStatus() {
        $pluginStatus = array();
        foreach (ZMPlugins::instance()->getAllPlugins(null, false) as $plugin) {
            $pluginStatus[$plugin->getId()] = array(
                'type' => $this->getPluginType($plugin),
                'scope' => $plugin->getScope(),
                'installed' => $plugin->isInstalled(),
                'enabled' => $plugin->isEnabled(),
                'context' => $plugin->getContext(),
                'order' => $plugin->getSortOrder()
            );
        }
        // update in db
        ZMLogging::instance()->log('updating plugin status...', ZMLogging::TRACE);
        ZMConfig::instance()->updateConfigValue('ZENMAGICK_PLUGIN_STATUS', serialize($pluginStatus));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $action = $request->getParameter('action');
        $pluginId = $request->getParameter('pluginId');

        $viewId = null;

        if ('upgrade' == $action) {
            if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
                ZMLogging::instance()->log('upgrade plugin: '.$plugin->getId(), ZMLogging::TRACE);
                $plugin->upgrade();
                ZMMessages::instance()->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                ZMMessages::instance()->addAll($plugin->getMessages());
                $viewId = 'success-upgrade';
            }
        } else if ('edit' == $action) {
            if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
                return $this->findView('plugin-conf', array('plugin' => $plugin));
            }
        }

        $this->refreshPluginStatus();
        return $this->findView($viewId);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($request->handleDemo()) {
            return $this->findView('success-demo');
        }

        $action = $request->getParameter('action');
        $multiAction = $request->getParameter('multiAction');
        $pluginId = $request->getParameter('pluginId');
        $multiPluginId = $request->getParameter('multiPluginId');

        // convert single action into multi
        if (null != $action && null != $pluginId) {
            $multiPluginId = array($pluginId);
        } else {
            $action = $multiAction;
            $multiPluginId = explode(',', $multiPluginId);
        }

        $viewId = null;

        foreach ($multiPluginId as $pluginId) {
            if ('install' == $action) {
                if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false)) && !$plugin->isInstalled()) {
                    ZMLogging::instance()->log('install plugin: '.$plugin->getId(), ZMLogging::TRACE);
                    $plugin->install();
                    ZMMessages::instance()->success(sprintf(_zm('Plugin %s installed successfully'), $plugin->getName()));
                    ZMMessages::instance()->addAll($plugin->getMessages());
                    $viewId = 'success-install';
                } else {
                }
            } else if ('uninstall' == $action) {
                $keepSettings = ZMLangUtils::asBoolean($request->getParameter('keepSettings', false));
                if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, true)) && $plugin->isInstalled()) {
                    ZMLogging::instance()->log('un-install plugin: '.$plugin->getId() . '; keepSettings: '.($keepSettings?'true':'false'), ZMLogging::TRACE);
                    $plugin->remove($keepSettings);
                    ZMMessages::instance()->success(sprintf(_zm('Plugin %s un-installed successfully'), $plugin->getName()));
                    ZMMessages::instance()->addAll($plugin->getMessages());
                    $viewId = 'success-uninstall';
                }
            } else if ('upgrade' == $action) {
                if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, true)) && $plugin->isInstalled()) {
                    ZMLogging::instance()->log('upgrade plugin: '.$plugin->getId(), ZMLogging::TRACE);
                    $plugin->upgrade();
                    ZMMessages::instance()->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                    ZMMessages::instance()->addAll($plugin->getMessages());
                    $viewId = 'success-upgrade';
                }
            } else if ('update' == $action) {
                if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
                    foreach ($plugin->getConfigValues() as $widget) {
                        if ($widget instanceof ZMFormWidget && null !== ($value = $request->getParameter($widget->getName()))) {
                            if (!$widget->compare($value)) {
                                // value changed, use widget to (optionally) format value
                                $widget->setValue($value);
                                $plugin->set($widget->getName(), $widget->getStringValue());
                            }
                        }
                    }
                }
            }
        }

        // do this last once all changes are made
        $this->refreshPluginStatus();
        return $this->findView($viewId);
    }

}
