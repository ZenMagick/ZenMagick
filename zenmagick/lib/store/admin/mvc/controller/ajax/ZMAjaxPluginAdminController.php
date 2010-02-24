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
class ZMAjaxPluginAdminController extends ZMAjaxController {
    private $response_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ajaxAdmin');
        $this->response_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Report.
     *
     * @param string msg The message.
     * @param string type The message type.
     */
    protected function report($msg, $type) {
        if (!array_key_exists($type, $this->response_)) {
            $response_[$type] = array();
        }
        $this->response_[$type][] = $msg;
    }

    /**
     * Enable/disable the given plugin.
     *
     * @param string pluginId The plugin id.
     * @param boolean status The new status.
     * @return boolean <code>true</code> if the status was set, <code>false</code> for any error.
     */
    protected function updatePluginStatus($pluginId, $status) {
        $plugin = ZMPlugins::instance()->initPluginForId($pluginId, false);
        if (null == $plugin) {
            $this->report('Invalid plugin id', 'error');
            $this->response_['pluginId'] = $pluginId;
            return false;
        }
        $plugin->setEnabled($status);
        return true;
    }

    /**
     * Install plugin.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>pluginId - The id of the plugin to enable.</li>
     * </ul>
     */
    public function installPluginJSON($request) {
        $pluginId = $request->getParameter('pluginId');

        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            if (!$plugin->isInstalled()) {
                $plugin->install();
                foreach ($plugin->getMessages() as $msg) {
                    $this->report($msg, 'info');
                }
                $this->report('Plugin installed', 'success');
                $this->response_['pluginId'] = $pluginId;
            } else {
                $this->report('Plugin already installed', 'error');
                $this->response_['pluginId'] = $pluginId;
            }
        } else {
            $this->report('Invalid plugin id: '.$pluginId, 'error');
            $this->response_['pluginId'] = $pluginId;
        }

        $flatObj = $this->flattenObject($this->response_);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * cwRemove plugin.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>pluginId - The id of the plugin to enable.</li>
     * </ul>
     */
    public function removePluginJSON($request) {
        $pluginId = $request->getParameter('pluginId');

        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($remove, true)) && $plugin->isInstalled()) {
            $plugin->remove();
            ZMMessages::instance()->addAll($plugin->getMessages());
        }
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            if ($plugin->isInstalled()) {
                $plugin->remove();
                foreach ($plugin->getMessages() as $msg) {
                    $this->report($msg, 'info');
                }
                $this->report('Plugin removed', 'success');
                $this->response_['pluginId'] = $pluginId;
            } else {
                $this->report('Plugin not installed', 'error');
                $this->response_['pluginId'] = $pluginId;
            }
        } else {
            $this->report('Invalid plugin id: '.$pluginId, 'error');
            $this->response_['pluginId'] = $pluginId;
        }

        $flatObj = $this->flattenObject($this->response_);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
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
    public function setPluginStatusJSON($request) {
        $pluginId = $request->getParameter('pluginId');
        $status = ZMLangUtils::asBoolean($request->getParameter('status'));

        if ($this->updatePluginStatus($pluginId, $status)) {
            $this->report('Status updated', 'success');
            $this->response_['pluginId'] = $pluginId;
        }

        $flatObj = $this->flattenObject($this->response_);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}
