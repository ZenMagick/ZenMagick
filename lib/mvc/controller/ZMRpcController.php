<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;
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
    public function process(ZMRequest $request) {
        $rpcRequest = ZMAjaxUtils::createRpcRequest($request);
        $method = $sacsMethod = $rpcRequest->getMethod();

        $rpcResponse = null;

        $sacsManager = $this->container->get('sacsManager');
        // check access on controller level
        if (!$sacsManager->authorize($request, $request->getRequestId(), $request->getUser(), false)) {
            $rpcResponse = $this->invalidCredentials($rpcRequest);
        }

        // (re-)check on method level if mapping exists
        $methodRequestId = $request->getRequestId().'#'.$sacsMethod;
        if ($sacsManager->hasMappingForRequestId($methodRequestId)) {
            if (!$sacsManager->authorize($request, $methodRequestId, $request->getUser(), false)) {
                $rpcResponse = $this->invalidCredentials($rpcRequest);
            }
        }

        if (!$rpcResponse) {
            if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
                Runtime::getLogging()->log('calling method: '.$method, Logging::TRACE);
                $rpcResponse = $this->$method($rpcRequest);
            } else {
                $rpcResponse = $rpcRequest->createResponse();
                $rpcResponse->setStatus(false);
                Runtime::getLogging()->error("Invalid request - method '".$request->getParameter('method')."' not found!");
            }
        }

        // set content type
        ZMNetUtils::setContentType($rpcResponse->getContentType());
        // the response
        echo $rpcResponse;
        return null;
    }

    /**
     * Build invalid credentials response.
     *
     * @param ZMRpcRequest rpcRequest The request.
     * @return ZMRpcResponse A response.
     */
    public function invalidCredentials($rpcRequest) {
        $request = $rpcRequest->getRequest();
        $rpcResponse = $rpcRequest->createResponse();
        if (null === $request->getUser()) {
            $rpcResponse->setStatus(false, ZMRpcResponse::RC_NO_CREDENTIALS);
            $rpcResponse->addMessage(_zm('No credentials'), 'error');
            $rpcResponse->setData(array('location' => $request->url(Runtime::getSettings()->get('zenmagick.http.request.login', 'login'), '', true)));
        } else {
            $rpcResponse->setStatus(false, ZMRpcResponse::RC_INVALID_CREDENTIALS);
            $rpcResponse->addMessage(_zm('Invalid credentials'), 'error');
        }
        return $rpcResponse;
    }

}
