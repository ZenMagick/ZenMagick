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
?>
<?php
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\logging\Logging;
use zenmagick\http\widgets\form\FormWidget;

/**
 * Admin controller for plugins.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PluginsController extends \ZMController {
    private static $TYPE_MAP = array(
        'order_total' => 'ZMOrderTotal',
        'payment' => 'ZMPaymentType'
    );


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        // TODO: make flat list
        $pluginList['general'] = $this->container->get('pluginService')->getAllPlugins(0, false);
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
        foreach ($this->container->get('pluginService')->getAllPlugins(null, false) as $plugin) {
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
        Runtime::getLogging()->log('updating plugin status...', Logging::TRACE);
        $this->container->get('configService')->updateConfigValue('ZENMAGICK_PLUGIN_STATUS', serialize($pluginStatus));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $action = $request->getParameter('action');
        $pluginId = $request->getParameter('pluginId');

        $viewId = null;

        if ('upgrade' == $action) {
            if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
                Runtime::getLogging()->log('upgrade plugin: '.$plugin->getId(), Logging::TRACE);
                $plugin->upgrade();
                $this->messageService->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                $this->messageService->addAll($plugin->getMessages());
                $viewId = 'success-upgrade';
            }
        } else if ('edit' == $action) {
            if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
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
                if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, false)) && !$plugin->isInstalled()) {
                    Runtime::getLogging()->log('install plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->install();
                    $this->messageService->success(sprintf(_zm('Plugin %s installed successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-install';
                } else {
                }
            } else if ('uninstall' == $action) {
                $keepSettings = Toolbox::asBoolean($request->getParameter('keepSettings', false));
                if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, true)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('un-install plugin: '.$plugin->getId() . '; keepSettings: '.($keepSettings?'true':'false'), Logging::TRACE);
                    $plugin->remove($keepSettings);
                    $this->messageService->success(sprintf(_zm('Plugin %s un-installed successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-uninstall';
                }
            } else if ('upgrade' == $action) {
                if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, true)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('upgrade plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->upgrade();
                    $this->messageService->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-upgrade';
                }
            } else if ('update' == $action) {
                if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
                    foreach ($plugin->getConfigValues() as $widget) {
                        if ($widget instanceof FormWidget && null !== ($value = $request->getParameter($widget->getName()))) {
                            if (!$widget->compare($value)) {
                                // value changed, use widget to (optionally) format value
                                $widget->setValue($value);
                                $plugin->set($widget->getName(), $widget->getStringValue());
                            }
                        }
                    }
                }
            } else if ('enable' == $action) {
                if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, false)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('enable plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->setEnabled(true);
                    $this->messageService->success(sprintf(_zm('Plugin %s enabled successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-enable';
                }
            } else if ('disable' == $action) {
                if (null != ($plugin = $this->container->get('pluginService')->initPluginForId($pluginId, true)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('disable plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->setEnabled(false);
                    $this->messageService->success(sprintf(_zm('Plugin %s disabled successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-disable';
                }
            }
        }

        // do this last once all changes are made
        $this->refreshPluginStatus();
        return $this->findView($viewId);
    }

}
