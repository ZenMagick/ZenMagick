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
 * Application context.
 *
 * @author DerManoMann
 * @package org.zenmagick.core
 */
class ZMApplicationContext extends ZMObject {
    private $definitions_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->definitions_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Load context from a YAML style string.
     *
     * @param string yaml The yaml style mappings.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function load($yaml, $override=true) {
        $this->definitions_ = ZMRuntime::yamlLoad($yaml, $this->definitions_, $override);
    }

    /**
     * Get a bean definition.
     *
     * @param string name The bean name.
     * @return string A bean definition or <code>null</code>.
     */
    public function getDefinition($name) {
        if (array_key_exists($name, $this->definitions_)) {
            return $this->definitions_[$name];
        }
        return null;
    }

}
