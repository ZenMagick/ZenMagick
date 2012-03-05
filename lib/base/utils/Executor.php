<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright 2006-2007 by Dick Munroe, Cottage Software Works, Inc.
 * Copyright (C) 2011 zenmagick.org
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
namespace zenmagick\base\utils;

use zenmagick\base\Runtime;

/**
 * An executor.
 *
 * <p>Extended <code>Callable</code> in that it also contains the (optional) method parameter.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class Executor {
    private $callback;
    private $parameter;
    private $parameterMapper;


    /**
     * Create a new instance.
     *
     * @param mixed callback The callback; default is <code>null</code>.
     * @param array parameter Optional parameter; default is an empty array.
     * @param ParameterMapper parameterMapper Optional parameter mapper for dynamic parameter resolution; defaultis null.
     */
    public function __construct($callback, array $parameter=array(), ParameterMapper $parameterMapper=null) {
        $this->callback = $callback;
        $this->parameter = $parameter;
        $this->parameterMapper = $parameterMapper;
    }

    /**
     * Set the callback.
     *
     * @param mixed target The instance/class of the callback <strong>or</strong> an actual callback (<code>array</code>).</p>
     * @param string method The method; ignored if <code>target</code> is an array; default is <code>null</code>.
     */
    public function setCallback($target, $method=null) {
        if (is_array($target)) {
            $this->callback = $target;
        } else {
            $this->callback = array($target, $method);
        }
    }

    /**
     * Set parameter.
     *
     * @param array parameter The call parameter.
     */
    public function setParameter(array $parameter) {
        $this->parameter = $parameter;
    }

    /**
     * Set parameter mapper.
     *
     * @param ParameterMapper parameterMapper A parameter mapper for dynamic parameter resolution.
     */
    public function setParameterMapper(ParameterMapper $parameterMapper) {
        $this->parameterMapper = $parameterMapper;
    }

    /**
     * Execute this.
     *
     * @return mixed The execution result.
     */
    public function execute() {
        $parameter = $this->parameter;

        if (null != $this->parameterMapper) {
            $parameter = $this->parameterMapper->mapParameter($this->callback, $parameter);
        }

        Runtime::getLogging()->debug(sprintf('executing %s', get_class($this->callback[0])));
        return call_user_func_array($this->callback, $parameter);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() {
        // todo: this only works for array callbacks
        $callback = get_class($this->callback[0]).', '.$this->callback[1];
        return '['.get_class($this).' callback=('.$callback.')]';
    }

}
