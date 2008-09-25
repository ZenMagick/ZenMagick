<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * A result source based on calling a method on an object.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.resultlist.sources
 * @version $Id$
 */
class ZMObjectResultSource extends ZMObject implements ZMResultSource {
    private $resultClass_;
    private $object_;
    private $method_;
    private $args_;


    /**
     * Create a new instance.
     *
     * @param string resultClass The class of the results.
     * @param mixed object The object to be used.
     * @param string method The method to call on the object.
     * @param array args Optional method parameter.
     */
    public function __construct($resultClass, $object, $method, $args=array()) {
        parent::__construct();
        $this->resultClass_ = $resultClass;
        $this->object_ = $object;
        $this->method_ = $method;
        $this->args_ = $args;
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }


 
    /**
     * {@inheritDoc}
     */
    public function setResultList($resultList) { /* not used */ }

    /**
     * {@inheritDoc}
     */
    public function getResults() {
        return call_user_func_array(array($this->object_, $this->method_), $this->args_);
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass() {
        return $this->resultClass_;
    }

    /**
     * Get the method name.
     *
     * @return string The method name.
     */
    public function getMethod() {
        return $this->method_;
    }

    /**
     * Get the method parameter.
     *
     * @return array The method parameter.
     */
    public function getArgs() {
        return $this->args_;
    }

}

?>
