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

use ZenMagick\Base\Toolbox;
use ZenMagick\apps\store\Plugins\PluginOptionsLoader;

/**
 * Ajax plugin admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxPluginAdminController extends \ZMRpcController {

    /**
     * Update plugin status.
     */
    public function setPluginStatus($rpcRequest) {
        $data = $rpcRequest->getData();
        $pluginId = $data->pluginId;
        $status = Toolbox::asBoolean($data->status);

        $rpcResponse = $rpcRequest->createResponse();

        $pluginService = $this->container->get('pluginService');

        if (null == ($plugin = $pluginService->getPluginForId($pluginId, true))) {
            $rpcResponse->setStatus(false);
            $rpcResponse->addMessage(_zm('Invalid plugin id'), 'error');
        } else {
            $configPrefix = PluginsController::prefix($plugin);
            $configService = $this->container->get('configService');
            $this->container->get('configService')->updateConfigValue($configPrefix.PluginOptionsLoader::KEY_ENABLED, $status);
            $rpcResponse->addMessage(_zm('Status updated'), 'success');
        }

        $this->container->get('pluginStatusMapBuilder')->getStatusMap(true);
        return $rpcResponse;
    }

}
