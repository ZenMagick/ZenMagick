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

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $pluginList = $this->container->get('pluginService')->getPluginsForContext(null, false);
        return array('pluginList' => $pluginList);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $action = $request->getParameter('action');
        $pluginId = $request->getParameter('pluginId');

        $viewId = null;
        $pluginService = $this->container->get('pluginService');
        // ensure we load all plugins
        $pluginService->getPluginsForContext(null);

        if ('upgrade' == $action) {
            if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                Runtime::getLogging()->log('upgrade plugin: '.$plugin->getId(), Logging::TRACE);
                $plugin->upgrade();
                $this->messageService->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                $this->messageService->addAll($plugin->getMessages());
                $viewId = 'success-upgrade';
            }
        } else if ('edit' == $action) {
            if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                return $this->findView('plugin-conf', array('plugin' => $plugin));
            }
        }

        $pluginService->refreshStatusMap();
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

        $pluginService = $this->container->get('pluginService');
        // force loading all
        $pluginService->getPluginsForContext(null, false);

        $viewId = null;

        foreach ($multiPluginId as $pluginId) {
            if ('install' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && !$plugin->isInstalled()) {
                    Runtime::getLogging()->log('install plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->install();
                    $this->messageService->success(sprintf(_zm('Plugin %s installed successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-install';
                }
            } else if ('uninstall' == $action) {
                $keepSettings = Toolbox::asBoolean($request->getParameter('keepSettings', false));
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('un-install plugin: '.$plugin->getId() . '; keepSettings: '.($keepSettings?'true':'false'), Logging::TRACE);
                    $plugin->remove($keepSettings);
                    $this->messageService->success(sprintf(_zm('Plugin %s un-installed successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-uninstall';
                }
            } else if ('upgrade' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('upgrade plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->upgrade();
                    $this->messageService->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-upgrade';
                }
            } else if ('update' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
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
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('enable plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->setEnabled(true);
                    $this->messageService->success(sprintf(_zm('Plugin %s enabled successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-enable';
                }
            } else if ('disable' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    Runtime::getLogging()->log('disable plugin: '.$plugin->getId(), Logging::TRACE);
                    $plugin->setEnabled(false);
                    $this->messageService->success(sprintf(_zm('Plugin %s disabled successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-disable';
                }
            }
        }

        // do this last once all changes are made
        $pluginService->refreshStatusMap();
        return $this->findView($viewId);
    }

}
