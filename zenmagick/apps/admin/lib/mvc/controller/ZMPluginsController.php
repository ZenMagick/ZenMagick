<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Admin controller for plugins.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMPluginsController extends ZMController {

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
        $pluginList = ZMPlugins::instance()->getAllPlugins(0, false);
        foreach ($pluginList as $group => $plugins) {
            // remove empty groups
            if (0 == count($plugins)) {
                unset($pluginList[$group]);
            }
        }

        return array('pluginList' => $pluginList);
    }

    /**
     * Refresh plugin status data.
     */
    protected function refreshPluginStatus() {
        $pluginStatus = array();
        foreach (ZMPlugins::instance()->getAllPlugins(0, false) as $group => $plugins) {
            foreach ($plugins as $plugin) {
                $pluginStatus[$plugin->getId()] = array(
                    'group' => $plugin->getGroup(),
                    'scope' => $plugin->getScope(),
                    'installed' => $plugin->isInstalled(),
                    'enabled' => $plugin->isEnabled(),
                    'context' => $plugin->getContext(),
                    'order' => $plugin->getSortOrder()
                );
            }
        }
        // update in db
        ZMLogging::instance()->log('updating plugin status...', ZMLogging::TRACE);
        ZMConfig::instance()->updateConfigValue('ZENMAGICK_PLUGIN_STATUS', serialize($pluginStatus));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $this->refreshPluginStatus();
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $action = $request->getParameter('action');
        $pluginId = $request->getParamter('pluginId');
        $group = $request->getParamter('group');
        if ('install' == $action) {
          echo 'install';
          die();
            if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false)) && !$plugin->isInstalled()) {
                $plugin->install();
                ZMMessages::instance()->success(zm_l10n_get('Plugin %s installed successfully', $plugin->getName()));
                ZMMessages::instance()->addAll($plugin->getMessages());
                return $this->findView('success-install');
            } else {
            }
        }
        return $this->findView();
        // TODO: process...







    $install = $request->getParameter('install');
    $remove = $request->getParameter('remove');
    $edit = $request->getParameter('edit');
    $group = $request->getParameter('group');
    $select = $request->getParameter('select');
    $refresh = '';
    $needRefresh = false;
    $editPlugin = null;
    if (null != ($request->getParameter('install'))) {
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($install, false)) && !$plugin->isInstalled()) {
            $plugin->install();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        $edit = $install;
        $editPlugin = $plugin;
        $needRefresh = true;
        $refresh = $edit;
    } else if (null != $remove) {
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($remove, false)) && $plugin->isInstalled()) {
            $plugin->remove();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        $needRefresh = true;
    } else if (null != $edit) {
        $editPlugin = ZMPlugins::instance()->initPluginForId($edit, false);
    } else if (null != $select) {
        $edit = $select;
        $editPlugin = ZMPlugins::instance()->initPluginForId($select, false);
    }

    // update
    if ('POST' == $request->getMethod() && null !== ($pluginId = $request->getParameter('pluginId'))) {
        $plugin = ZMPlugins::instance()->initPluginForId($pluginId, false);
        foreach ($plugin->getConfigValues() as $widget) {
            if ($widget instanceof ZMFormWidget && null !== ($value = $request->getParameter($widget->getName()))) {
                if (!$widget->compare($value)) {
                    // value changed, use widget to (optionally) format value
                    $widget->setValue($value);
                    $plugin->set($widget->getName(), $widget->getStringValue());
                }
            }
        }
        $refresh = $pluginId;
        $needRefresh = true;
        $editPlugin = $plugin;
    }

    if ($needRefresh) {
        $fragment = '';
        if ($editPlugin) {
            $fragment = '#' . $editPlugin->getId();
        }
        $request->redirect($admin2->url(null, 'select='.$refresh.$fragment, true));
    }


















        $this->refreshPluginStatus();
        return $this->findView('success');
    }

}
