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

namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\Http\Request;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\HttpFoundation\Response;

/**
 * Request controller for ajax requests.
 *
 * <p>Uses native PHP function <code>json_encode</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @deprecated use RpcController instead
 */
class AjaxController extends DefaultController
{
    /**
     * Process a HTTP GET request.
     *
     * <p>Just return <code>null</code>.</p>
     */
    public function processGet($request)
    {
        $this->container->get('logger')->err("Invalid Ajax request - method '".$request->getParameter('method')."' not found!");

        return null;
    }

    /**
     * Process a HTTP request.
     *
     * <p>This implementation will delegate request handling based on the method parameter in
     * the request. If no method is found, the default <em>parent</em> <code>process()</code> implementation
     * will be called.</p>
     *
     * <p>Also, if the passed method is not found, the controller will try to resolve the method by appending the
     * configured <em>ajaxFormat</em> string. So, if, for example, the method is <code>getCountries</code> and <em>ajaxFormat</em> is
     * <code>JSON</code>, the controller will first look for <code>getCountries</code> and then for <code>getCountriesJSON</code>.</p>
     *
     * @return View A <code>View</code> instance or <code>null</code>.
     */
    public function processAction(Request $request)
    {
        $method = $sacsMethod = $request->getParameter('method');
        if (!method_exists($this, $method)) {
            $method = $method.'JSON';
        }

        $sacsManager = $this->container->get('sacsManager');
        // check access on controller level
        $sacsManager->authorize($request, $request->getRequestId(), $this->getUser());

        // (re-)check on method level if mapping exists
        $methodRequestId = $request->getRequestId().'#'.$sacsMethod;
        if ($sacsManager->hasMappingForRequestId($methodRequestId)) {
            $sacsManager->authorize($request, $methodRequestId, $this->getUser());
        }

        if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
            $this->$method($request);

            return null;
        }

        return parent::processAction($request);
    }

    /**
     * Set JSON response header ('X-JSON').
     *
     * @param string json The JSON data.
     */
    public function setJSONHeader($json)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContent($json);

        return $response;
    }
}
