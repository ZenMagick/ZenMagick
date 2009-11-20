<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Form data container.
 *
 * <p>Using <code>addTables()</code> allows to extend the list of fields with all custom fields
 * configured for the listes tables. This allows capturing and handling custom table columns in the used
 * form.</p> 
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc
 * @version $Id$
 */
class FormData extends ZMFormData {
    protected $tables_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->tables_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the name(s) of the tables to be used to populate custom fields.
     *
     * @param mixed tables Either an array or comma separated list of table names (without the prefix).
     */
    public function addTables($tables) {
        if (!is_array($tables)) {
            $tables = explode(',', $tables);
        }
        $this->tables_ = array_merge($this->tables_, $tables);
    }

    /**
     * Populate this form.
     *
     * @param ZMRequest request The request to process.
     */
    public function populate($request) {
        $fields = null;
        if (0 < count($this->fields_)) {
            // add custom table based names to the fields list, but only if we restrict fields anyway
            foreach ($this->tables_ as $table) {
                $fields = array_keys(ZMDbTableMapper::instance()->getCustomFieldInfo($table));
                $this->fields_ = array_merge($this->fields_, $fields);
            }
            $fields = $this->fields_;
        }

        ZMBeanUtils::setAll($this, $request->getParameterMap(), $fields);
    }

}

?>
