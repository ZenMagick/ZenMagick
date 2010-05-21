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


/**
 * Scaffold controller.
 *
 * <p>Allows to execute a specific method based on request parameters.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.controller
 * @version $Id$
 */
class ZMScaffoldController extends ZMController {

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
        $method = $sacsMethod = $request->getParameter('method');

        // check access on controller level
        ZMSacsManager::instance()->authorize($request, $request->getRequestId(), $request->getUser());

        // (re-)check on method level if mapping exists
        $methodRequestId = $request->getRequestId().'#'.$sacsMethod;
        if (ZMSacsManager::instance()->hasMappingForRequestId($methodRequestId)) {
            ZMSacsManager::instance()->authorize($request, $methodRequestId, $request->getUser());
        }

        if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
            $this->$method($request);
            return null;
        }

        ZMLogging::instance()->trace("Invalid request - method '".$request->getParameter('method')."' not found!", ZMLogging::ERROR);
        return null;
    }

}
