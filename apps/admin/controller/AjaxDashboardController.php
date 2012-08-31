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
namespace ZenMagick\apps\admin\controller;

/**
 * Ajax dashboard controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxDashboardController extends \ZMRpcController {

    /**
     * Save state.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function saveState($rpcRequest) {
        $state = json_encode($rpcRequest->getData());

        $rpcResponse = $rpcRequest->createResponse();
        $this->container->get('dashboard')->setState($this->getUser()->getId(), $state);
        $rpcResponse->setStatus(true);

        return $rpcResponse;
    }

    /**
     * Lookup update information.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function getUpdateInfo($rpcRequest) {
        $versionUrl = 'http://www.zenmagick.org/version';
        $settingsService = $this->container->get('settingsService');

        if ($settingsService->exists('apps.store.update.channel')) {
            $versionUrl .= '/'.$settingsService->get('apps.store.update.channel');
        }
        $versionUrl .= '?current='.$settingsService->get('zenmagick.version');
        $latest = file_get_contents($versionUrl);

        $rpcResponse = $rpcRequest->createResponse();
        $rpcResponse->setStatus(true);
        $rpcResponse->setData(trim($latest));

        return $rpcResponse;
    }

}
