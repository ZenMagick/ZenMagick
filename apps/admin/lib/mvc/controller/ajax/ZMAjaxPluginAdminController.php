<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package zenmagick.store.admin.mvc.controller.ajax
 */
class ZMAjaxPluginAdminController extends ZMRpcController {

    /**
     * Install plugin.
     */
    public function installPlugin($rpcRequest) {
        $pluginId = $rpcRequest->getData()->pluginId;

        $rpcResponse = $rpcRequest->createResponse();

        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            if (!$plugin->isInstalled()) {
                $plugin->install();
                foreach ($plugin->getMessages() as $msg) {
                    $rpcResponse->addMessage($msg, 'info');
                }
                $rpcResponse->setStatus(true);
                $rpcResponse->setData(array('pluginId' => $pluginId, 'hasOptions', $plugin->hasOptions()));
                $rpcResponse->addMessage(_zm('Plugin installed'), 'success');
            } else {
                $rpcResponse->setStatus(false);
                $rpcResponse->addMessage(_zm('Plugin already installed'), 'error');
            }
        } else {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage(_zm('Invalid plugin id'), 'error');
        }

        return $rpcResponse;
    }

    /**
     * Remove plugin.
     */
    public function removePlugin($rpcRequest) {
        $pluginId = $rpcRequest->getData()->pluginId;

        $rpcResponse = $rpcRequest->createResponse();

        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($remove, true)) && $plugin->isInstalled()) {
            $plugin->remove();
            foreach ($plugin->getMessages() as $msg) {
                $rpcResponse->addMessage($msg, 'info');
            }
        }
        if (null != ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            if ($plugin->isInstalled()) {
                $plugin->remove();
                $rpcResponse->setStatus(true);
                foreach ($plugin->getMessages() as $msg) {
                    $rpcResponse->addMessage($msg, 'info');
                }
                $rpcResponse->addMessage(_zm('Plugin removed'), 'success');
            } else {
                $rpcResponse->setStatus(false);
                $rpcResponse->addMessage(_zm('Plugin not installed'), 'error');
            }
        } else {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage(_zm('Invalid plugin id'), 'error');
        }

        return $rpcResponse;
    }

    /**
     * Update plugin status.
     */
    public function setPluginStatus($rpcRequest) {
        $data = $rpcRequest->getData();
        $pluginId = $data->pluginId;
        $status = ZMLangUtils::asBoolean($data->status);

        $rpcResponse = $rpcRequest->createResponse();

        if (null == ($plugin = ZMPlugins::instance()->initPluginForId($pluginId, false))) {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage(_zm('Invalid plugin id'), 'error');
        } else {
            $rpcResponse->setStatus(true);
            $plugin->setEnabled($status);
            $rpcResponse->addMessage(_zm('Status updated'), 'success');
        }

        return $rpcResponse;
    }

}
