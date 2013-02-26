<?php
/*
 * ZenMagick - Another PHP framework.
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

namespace ZenMagick\ZenMagickBundle\Controller;

use ZenMagick\Base\Beans;
use ZenMagick\Http\Request;
use ZenMagick\Http\Sacs\SacsManager;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\HttpFoundation\Response;

/**
 * RPC controller.
 *
 * @todo drop this in favor of the DefaultController
 * @author DerManoMann <mano@zenmagick.org>
 */
class RpcController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processAction(Request $request)
    {
        $format = $this->container->get('settingsService')->get('zenmagick.mvc.rpc.format', 'JSON');
        $rpcRequest = Beans::getBean('ZMRpcRequest'.$format);
        $rpcRequest->setRequest($request);

        $method = $sacsMethod = $rpcRequest->getMethod();

        $rpcResponse = null;

        $sacsManager = $this->container->get('sacsManager');
        // check access on controller level
        if (!$sacsManager->authorize($request, $request->getRequestId(), $this->getUser(), false)) {
            $rpcResponse = $this->invalidCredentials($rpcRequest);
        }

        // (re-)check on method level if mapping exists
        $methodRequestId = $request->getRequestId().'#'.$sacsMethod;
        if ($sacsManager->hasMappingForRequestId($methodRequestId)) {
            if (!$sacsManager->authorize($request, $methodRequestId, $this->getUser(), false)) {
                $rpcResponse = $this->invalidCredentials($rpcRequest);
            }
        }

        if (!$rpcResponse) {
            if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
                $this->get('logger')->debug('calling method: '.$method);
                $rpcResponse = $this->$method($rpcRequest);
            } else {
                $rpcResponse = $rpcRequest->createResponse();
                $rpcResponse->setStatus(false);
                $this->container->get('logger')->err("Invalid request - method '".$request->getParameter('method')."' not found!");
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', $rpcResponse->getContentType());
        $response->setContent($rpcResponse);

        return $response;
    }

    /**
     * Build invalid credentials response.
     *
     * @param ZMRpcRequest rpcRequest The request.
     * @return ZMRpcResponse A response.
     */
    public function invalidCredentials($rpcRequest)
    {
        $request = $rpcRequest->getRequest();
        $rpcResponse = $rpcRequest->createResponse();
        if (null === $this->getUser()) {
            $rpcResponse->setStatus(false, \ZMRpcResponse::RC_NO_CREDENTIALS);
            $rpcResponse->addMessage(_zm('No credentials'), 'error');
            $rpcResponse->setData(array('location' => $this->container->get('netTool')->url($this->container->get('settingsService')->get('zenmagick.http.request.login', 'login'))));
        } else {
            $rpcResponse->setStatus(false, \ZMRpcResponse::RC_INVALID_CREDENTIALS);
            $rpcResponse->addMessage(_zm('Invalid credentials'), 'error');
        }

        return $rpcResponse;
    }

}
