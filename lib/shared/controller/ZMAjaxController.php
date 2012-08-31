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

use ZenMagick\Base\Runtime;
use ZenMagick\http\Request;
use ZenMagick\http\sacs\SacsManager;

/**
 * Request controller for ajax requests.
 *
 * <p>Uses native PHP function <code>json_encode</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.controller
 * @deprecated use ZMRpcController instead
 */
class ZMAjaxController extends ZMController {

    /**
     * Process a HTTP GET request.
     *
     * <p>Just return <code>null</code>.</p>
     */
    public function processGet($request) {
        Runtime::getLogging()->error("Invalid Ajax request - method '".$request->getParameter('method')."' not found!");
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
    public function process(Request $request) {
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

        return parent::process($request);
    }


    /**
     * Set JSON response header ('X-JSON').
     *
     * @param string json The JSON data.
     */
    public function setJSONHeader($json) {
        $this->setContentType('text/plain');
        echo $json;
    }

    /**
     * Serialize object to JSON.
     *
     * @param mixed obj The object to serialize; can also be an array of objects.
     * @return string The given object as JSON.
     */
    public function toJSON($obj) {
        return json_encode($obj);
    }

}
