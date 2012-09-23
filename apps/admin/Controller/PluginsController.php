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
namespace ZenMagick\apps\admin\Controller;

use Monolog\Logger;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Http\Widgets\Form\FormWidget;
use ZenMagick\StoreBundle\Plugins\PluginOptionsLoader;

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
        $pluginList = $this->container->get('pluginService')->getAllPlugins(false);
        return array('pluginList' => $pluginList);
    }

    /**
     * Prepare widgets for editing.
     *
     * @param array options The widget options.
     * @return array Widget map.
     * @todo make more generic?
     */
    protected function widgets(array $options) {
        $widgets = array();
        foreach ($options['properties'] as $name => $property) {
            $type = isset($property['type']) ? $property['type'] : 'text';
            // @todo: allow class, id as-is
            $id = sprintf('%sFormWidget', $type);
            if ($this->container->has($id)) {
                $widget = $this->container->get($id);
                Beans::setAll($widget, $property);
                if (isset($property['config'])) {
                    Beans::setAll($widget, $property['config']);
                }
                $widget->setName($name);
                $widgets[] = $widget;
            }
        }
        return $widgets;
    }

    /**
     * Get the config prefix for the given plugin.
     *
     * @param Plugin plugin The plugin.
     * @return string The prefix.
     */
    public static function prefix($plugin) {
        return strtoupper(PluginOptionsLoader::KEY_PREFIX . $plugin->getId() . '_');
    }

    /**
     * Remove plugin.
     *
     * @param Plugin plugin The plugin.
     * @param boolean keepSettings Flag to indicate whether to keep settings or not.
     */
    protected function remove($plugin, $keepSettings) {
        $plugin->remove($keepSettings);
        $configService = $this->container->get('configService');
        $configPrefix = self::prefix($plugin);

        // always remove
        $configService->removeConfigValue($configPrefix.PluginOptionsLoader::KEY_ENABLED);

        if (!$keepSettings) {
            $configService->removeConfigValues($configPrefix.'%');
        }
    }

    /**
     * Upgrade plugin.
     *
     * @param Plugin plugin The plugin.
     */
    protected function upgrade($plugin) {
        $this->remove($plugin, true);
        $this->install($plugin);
    }

    /**
     * Install plugin.
     *
     * @param Plugin plugin The plugin.
     */
    protected function install($plugin) {
        $configPrefix = self::prefix($plugin);
        $configService = $this->container->get('configService');

        // @todo db define!
        $group = ZENMAGICK_PLUGIN_GROUP_ID;

        // custom plugin install
        $plugin->install();

        // values for db
        $values = array(
            // system settings
            array('', $configPrefix.PluginOptionsLoader::KEY_ENABLED, true, $group),
            array('', $configPrefix.PluginOptionsLoader::KEY_SORT_ORDER, 0, $group)
        );

        // add options to db
        foreach ($this->widgets($plugin->getOptions()) as $widget) {
            $values[] = array('', $configPrefix.$widget->getName(), $widget->getValue(), $group);
        }

        // check for existing values...
        $currentKeys = array();
        foreach ($configService->getConfigValues($configPrefix.'%') as $value) {
            $currentKeys[] = $value->getKey();
        }

        foreach ($values as $value) {
            if (!in_array(strtoupper($value[1]), $currentKeys)) {
                call_user_func_array(array($configService, 'createConfigValue'), $value);
            }
        }
    }

    /**
     * Set plugin status.
     *
     * @param Plugin plugin The plugin.
     */
    protected function setStatus($plugin, $status) {
        $configPrefix = self::prefix($plugin);
        $configService = $this->container->get('configService');
        $this->container->get('configService')->updateConfigValue($configPrefix.PluginOptionsLoader::KEY_ENABLED, $status);
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

        if ('edit' == $action) {
            if (null != ($plugin = $pluginService->getPluginForId($pluginId, true)) && $plugin->isInstalled()) {
                return $this->findView('plugin-conf', array('plugin' => $plugin, 'widgets' => $this->widgets($plugin->getOptions())));
            }
            // @todo: message?
        }

        $this->container->get('pluginStatusMapBuilder')->getStatusMap(true);
        return $this->findView($viewId);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($this->handleDemo()) {
            return $this->findView('success-demo');
        }

        $action = $request->request->get('action');
        $multiAction = $request->request->get('multiAction');
        $pluginId = $request->request->get('pluginId');
        $multiPluginId = $request->request->get('multiPluginId');

        // convert single action into multi
        if (null != $action && null != $pluginId) {
            $multiPluginId = array($pluginId);
        } else {
            $action = $multiAction;
            $multiPluginId = explode(',', $multiPluginId);
        }

        $pluginService = $this->container->get('pluginService');
        // force loading all
        $pluginService->getAllPlugins(false);

        $viewId = null;
        $loggingService = $this->container->get('logger');

        foreach ($multiPluginId as $pluginId) {
            if ('install' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && !$plugin->isInstalled()) {
                    $loggingService->log('install plugin: '.$plugin->getId(), Logger::DEBUG);
                    $this->install($plugin);
                    $this->messageService->success(sprintf(_zm('Plugin %s installed successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-install';
                }
            } else if ('uninstall' == $action) {
                $keepSettings = Toolbox::asBoolean($request->request->get('keepSettings', false));
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    $loggingService->log('un-install plugin: '.$plugin->getId() . '; keepSettings: '.($keepSettings?'true':'false'), Logger::DEBUG);
                    $this->remove($plugin, $keepSettings);
                    $this->messageService->success(sprintf(_zm('Plugin %s un-installed successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-uninstall';
                }
            } else if ('upgrade' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    $loggingService->log('upgrade plugin: '.$plugin->getId(), Logger::DEBUG);
                    $this->upgrade($plugin);
                    $this->messageService->success(sprintf(_zm('Plugin %s upgraded successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-upgrade';
                }
            } else if ('update' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    $configPrefix = self::prefix($plugin);
                    $configService = $this->container->get('configWidgetService');
                    foreach ($this->widgets($plugin->getOptions()) as $widget) {
                        if ($widget instanceof FormWidget && null !== ($value = $request->request->get($widget->getName()))) {
                            if (!$widget->compare($value)) {
                                // value changed, use widget to (optionally) format value
                                $widget->setValue($value);
                                $configService->updateConfigValue(strtoupper($configPrefix.$widget->getName()), $widget->getStringValue());
                            }
                        }
                    }
                }
            } else if ('enable' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    $loggingService->log('enable plugin: '.$plugin->getId(), Logger::DEBUG);
                    $this->setStatus($plugin, true);
                    $this->messageService->success(sprintf(_zm('Plugin %s enabled successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-enable';
                }
            } else if ('disable' == $action) {
                if (null != ($plugin = $pluginService->getPluginForId($pluginId)) && $plugin->isInstalled()) {
                    $loggingService->log('disable plugin: '.$plugin->getId(), Logger::DEBUG);
                    $this->setStatus($plugin, false);
                    $this->messageService->success(sprintf(_zm('Plugin %s disabled successfully'), $plugin->getName()));
                    $this->messageService->addAll($plugin->getMessages());
                    $viewId = 'success-disable';
                }
            }
        }

        // do this last once all changes are made
        $this->container->get('pluginStatusMapBuilder')->getStatusMap(true);
        return $this->findView($viewId);
    }

}
