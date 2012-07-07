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
namespace zenmagick\http\controller;

use ReflectionClass;
use zenmagick\base\Beans;
use zenmagick\base\ZMObject;
use zenmagick\base\utils\ParameterMapper;
use zenmagick\http\Request;

/**
 * ParameterMapper for controller classes.
 *
 * <p>Parameters will be resolved in the following order:</p>
 * <ol>
 *   <li>Does the parameter name match a value key in router match data</li>
 *   <li>Does a type hint class exist and does that implement <code>zenmagick\http\forms\Form</code></li>
 *   <li>Is the parameter name a valid contaier service id</li>
 *   <li>Create new instance based on type hint and populate with request data</li>
 * </ol>
 *
 * <p>If none of the above applies, the parameter will be <code>null</code>.</p>
 * @author DerManoMann <mano@zenmagick.org>
 */
class ControllerParameterMapper extends ZMObject implements ParameterMapper {
    protected $request;

    /**
     * Set the request.
     *
     * @param zenmagick\http\Request request The request.
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function mapParameter($callback, array $parameter) {
        if (!is_array($callback)) {
            return $parameter;
        }

        $rc = new ReflectionClass($callback[0]);
        $method = $rc->getMethod($callback[1]);

        $mapped = array();
        foreach ($method->getParameters() as $rp) {
            $value = null;
            if (array_key_exists($rp->name, $parameter)) {
                $value = $parameter[$rp->name];
            } else {
                // check for known types
                $hintClass = $rp->getClass();
                if ($hintClass) {
                    // check for special classes/interfaces
                    if ('zenmagick\http\forms\Form' == $hintClass->name || $hintClass->isSubclassOf('zenmagick\http\forms\Form')) {
                        $value = Beans::getBean($hintClass->name);
                        $value->populate($this->request);
                    } else if ($this->container->has($rp->name)) {
                        if (($service = $this->container->get($rp->name)) instanceof $hintClass->name) {
                            $value = $service;
                        }
                    } else {
                        // last choice - assume a model class that does not extend/implement FormData
                        $value = Beans::getBean($hintClass->name);
                        Beans::setAll($value, $this->request->getParameterMap(), null);
                    }
                }
            }
            $mapped[] = $value;
        }
        return $mapped;
    }

}
