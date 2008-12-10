<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Model base class.
 *
 * <p>This class provides generic support for properties via <code>get($name)</code>, <code>set($name, $value)</code>
 * and, for PHP5, via the corresponding methods <code>__get($name)</code> and <code>__set($name,$value)</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick
 * @version $Id$
 */
class ZMModel extends ZMObject {
    protected $properties_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->properties_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    public function populate($req=null) {
    }

    /**
     * Populate custom fields from the given request.
     *
     * @param string table The table name.
     */
    public function populateCustom($table) {
        $fields = array_keys(ZMDbTableMapper::getCustomFieldInfo($table));
        $parameter = ZMRequest::getParameterMap();
        ZMBeanUtils::setAll($this, ZMRequest::getParameterMap(), $fields);
    }

}

?>
