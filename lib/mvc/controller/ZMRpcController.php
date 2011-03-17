<?php
/*
 * ZenMagick - Another PHP framework.
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

use zenmagick\http\sacs\SacsManager;

/**
 * RPC controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.controller
 */
class ZMRpcController extends ZMController {

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
    }


    /**
     * {@inheritDoc}
     */
    public function process($request) {
        $rpcRequest = ZMAjaxUtils::createRpcRequest($request);
        $method = $sacsMethod = $rpcRequest->getMethod();

        // check access on controller level
        SacsManager::instance()->authorize($request, $request->getRequestId(), $request->getUser());

        // (re-)check on method level if mapping exists
        $methodRequestId = $request->getRequestId().'#'.$sacsMethod;
        if (SacsManager::instance()->hasMappingForRequestId($methodRequestId)) {
            SacsManager::instance()->authorize($request, $methodRequestId, $request->getUser());
        }

        if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
            ZMLogging::instance()->log('calling method: '.$method, ZMLogging::TRACE);
            $rpcResponse = $this->$method($rpcRequest);
        } else {
            $rpcResponse = $rpcRequest->createResponse();
            $rpcResponse->setStatus(false);
            ZMLogging::instance()->trace("Invalid request - method '".$request->getParameter('method')."' not found!", ZMLogging::ERROR);
        }

        // set content type
        ZMNetUtils::setContentType($rpcResponse->getContentType());
        // the response
        echo $rpcResponse;
        return null;
    }

}
