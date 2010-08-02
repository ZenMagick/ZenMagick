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
 * Ajax dashboard controller.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller.ajax
 */
class ZMAjaxDashboardController extends ZMRpcController {

    /**
     * Save state.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function saveState($rpcRequest) {
        $state = json_encode($rpcRequest->getData());

        $rpcResponse = $rpcRequest->createResponse();
        ZMDashboard::setState($rpcRequest->getRequest()->getUser()->getId(), $state);
        $rpcResponse->setStatus(true);

        return $rpcResponse;
    }

    /**
     * Lookup update information.
     *
     * @param ZMRpcRequest rpcRequest The RPC request.
     */
    public function getUpdateInfo($rpcRequest) {
        $latest = file_get_contents("http://www.zenmagick.org/version.txt");

        $rpcResponse = $rpcRequest->createResponse();
        $rpcResponse->setStatus(true);
        $rpcResponse->setData(trim($latest));

        return $rpcResponse;
    }

}
