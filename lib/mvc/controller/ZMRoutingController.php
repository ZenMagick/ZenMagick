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

/**
 * Controller wrapper for method mapping of routing controllers.
 *
 * @author DerManoMann
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
    public function process($request) {
        // figure out parameters
        $rc = new ReflectionClass($this->controller);
        $method = $rc->getMethod($this->method);
        $parameters = array();
        foreach ($method->getParameters() as $rp) {
            $value = null;
            if (array_key_exists($rp->name, $this->args)) {
                $value = $this->args[$rp->name];
            }
            $parameters[] = $value;
        }

        return call_user_func_array(array($this->controller, $this->method), $parameters);
    }

}
