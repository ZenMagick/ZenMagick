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
 * Ajax plugin admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.mvc.controller.ajax
 * @version $Id$
 */
class ZMAjaxPluginAdminController extends ZMScaffoldController {

    /**
     * Install plugin.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>pluginId - The id of the plugin to enable.</li>
     * </ul>
     */
    public function installPlugin($request) {
        $pluginId = $request->getParameter('pluginId');

        $response = ZMAjaxUtils::getAjaxResponse();
        $response->set('pluginId', $pluginId);

        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            if (!$plugin->isInstalled()) {
                $plugin->install();
                foreach ($plugin->getMessages() as $msg) {
                    $response->addMessage($msg, 'info');
                }
                $response->setStatus(true);
                $response->addMessage('Plugin installed', 'success');
            } else {
                $response->setStatus(false);
                $response->addMessage('Plugin already installed', 'error');
            }
        } else {
            $response->setStatus(false);
            $response->addMessage('Invalid plugin id', 'error');
        }

        $response->createResponse($this);
        return $response->getStatus();
    }

    /**
     * cwRemove plugin.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>pluginId - The id of the plugin to enable.</li>
     * </ul>
     */
    public function removePlugin($request) {
        $pluginId = $request->getParameter('pluginId');

        $response = ZMAjaxUtils::getAjaxResponse();
        $response->set('pluginId', $pluginId);

        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($remove, true)) && $plugin->isInstalled()) {
            $plugin->remove();
            foreach ($plugin->getMessages() as $msg) {
                $response->addMessage($msg, 'info');
            }
        }
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            if ($plugin->isInstalled()) {
                $plugin->remove();
                $response->setStatus(true);
                foreach ($plugin->getMessages() as $msg) {
                    $response->addMessage($msg, 'info');
                }
                $response->addMessage('Plugin removed', 'success');
            } else {
                $response->setStatus(false);
                $response->addMessage('Plugin not installed', 'error');
            }
        } else {
            $response->setStatus(false);
            $response->addMessage('Invalid plugin id: '.$pluginId, 'error');
        }

        $response->createResponse($this);
        return $response->getStatus();
    }

    /**
     * Enable plugin.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>pluginId - The id of the plugin to enable.</li>
     *  <li>status - The new status as boolean.</li>
     * </ul>
     */
    public function setPluginStatus($request) {
        $pluginId = $request->getParameter('pluginId');
        $status = ZMLangUtils::asBoolean($request->getParameter('status'));

        $response = ZMAjaxUtils::getAjaxResponse();
        $response->set('pluginId', $pluginId);

        if (null == ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            $response->setStatus(false);
            $response->addMessage('Invalid plugin id', 'error');
        } else {
            $response->setStatus(true);
            $plugin->setEnabled($status);
            $response->addMessage('Status updated', 'success');
        }

        $response->createResponse($this);
        return $response->getStatus();
    }

}
