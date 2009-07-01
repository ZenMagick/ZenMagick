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
 * Base class for form data container.
 *
 * <p>Extending from this class allows to restrict the fields taken from the request to a given
 * list via <code>addFields()</code>.</p>
 *
 * <p>Furthermore, using <code>addTables()</code> allows to extend the list of fields with all custom fields
 * configured for the listes tables. This allows capturing and handling custom table columns in the used
 * form.</p> 
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc
 * @version $Id: ZMFormBean.php 2303 2009-06-23 02:36:01Z dermanomann $
 */
class ZMFormBean extends ZMObject {
    protected $tables_;
    protected $fields_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->tables_ = array();
        $this->fields_ = array();
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
     * Limit populating this form bean to the given fields.
     *
     *
     * @param mixed fields Either an array or comma separated list of field names.
     */
    public function addFields($fields) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $this->fields_ = array_merge($this->fields_, $fields);
    }

    /**
     * Populate this form.
     */
    public function populate() {
        $fields = null;
        if (0 < count($this->fields_)) {
            // add custom table based names to the fields list, but only if we restrict fields anyway
            foreach ($this->tables_ as $table) {
                $fields = array_keys(ZMDbTableMapper::instance()->getCustomFieldInfo($table));
                $this->fields_ = array_merge($this->fields_, $fields);
            }
            $fields = $this->fields_;
        }

        ZMBeanUtils::setAll($this, ZMRequest::getParameterMap(), $fields);
    }

}

?>
