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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Controller wrapper for method mapping of routing controllers.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.controller
 */
class ZMRoutingController extends ZMController {
    private $controller;
    private $method;
    private $args;

    /**
     * Create new instance.
     *
     * @param mixed controller The actual controller instance.
     * @param string method The method name.
     * @param array args Available method parameter.
     */
    public function __construct($controller, $method, $args) {
        parent::__construct();
        $this->controller = $controller;
        $this->method = $method;
        $this->args = $args;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ZMRequest $request) {
        // figure out parameters
        $rc = new ReflectionClass($this->controller);
        $method = $rc->getMethod($this->method);
        $parameters = array();
        foreach ($method->getParameters() as $rp) {
            $value = null;
            if (array_key_exists($rp->name, $this->args)) {
                $value = $this->args[$rp->name];
            } else {
                // check for known types
                $hintClass = $rp->getClass();
                if ($hintClass) {
                    // check for special classes/interfaces
                    // TODO: this is expected to grow a bit, so make the code look nicer
                    if ('ZMFormData' == $hintClass->name || $hintClass->isSubclassOf('ZMFormData')) {
                        $value = Beans::getBean($hintClass->name);
                        $value->populate($request);
                    } else if ('ZMRequest' == $hintClass->name || $hintClass->isSubclassOf('ZMRequest')) {
                        $value = $request;
                    } else if ('ZMMessages' == $hintClass->name || $hintClass->isSubclassOf('ZMMessages')) {
                        $value = $this->container->get('messageService');
                    } else {
                        // last choice - assume a model class that does not extend/implement FormData
                        $value = Beans::getBean($hintClass->name);
                        Beans::setAll($value, $request->getParameterMap(), null);
                    }
                }
            }
            $parameters[] = $value;
        }

        // check authorization
        $sacsManager = $this->container->get('sacsManager');
        $sacsManager->authorize($request, $request->getRequestId(), $request->getUser());

        // check method level too
        $methodRequestId = $request->getRequestId().'#'.$this->method;
        if ($sacsManager->hasMappingForRequestId($methodRequestId)) {
            $sacsManager->authorize($request, $methodRequestId, $request->getUser());
        }

        Runtime::getEventDispatcher()->dispatch('controller_process_start', new Event($this, array('request' => $request, 'controller' => $this->controller)));
        $view = call_user_func_array(array($this->controller, $this->method), $parameters);

        if (is_string($view)) {
            // just the viewId
            $view = $this->findView($view);
        }

        Runtime::getEventDispatcher()->dispatch('controller_process_end', new Event($this, array('request' => $request, 'controller' => $this->controller, 'view' => $view)));

        return $view;
    }

}
