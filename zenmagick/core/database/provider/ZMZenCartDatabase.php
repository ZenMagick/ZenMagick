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
 * Implementation of the ZenMagick database layer using zen-cart's <code>$db</code>.
 *
 * @author mano
 * @package org.zenmagick
 * @version $Id$
 */
class ZMZenCartDatabase extends ZMObject implements ZMDatabase {
    private $db_;


    /**
     * Create a new instance.
     */
    function __construct() {
    global $db;

        parent::__construct();
        $this->db_ = $db;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null) {
        $results = $this->query($sql, $args, $mapping, $modelClass);
        return 1 == count($results) ? $results[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null) {
        // TODO: cache mapping
        $mapping = $this->parseMapping($mapping);

        // bind query parameter
        foreach ($args as $name => $value) {
            $sql = $this->db_->bindVars($sql, ':'.$name, $value, $mapping[$name]['type']);
        }

        $results = array();
        $rs = $this->db_->Execute($sql);
        while (!$rs->EOF) {
            $result = $rs->fields;
            if (null != $mapping) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass) {
                $result = $this->map2model($modelClass, $result, $mapping);
            }

            $results[] = $result;
            $rs->MoveNext();
        }

        return $results;
    }

    /**
     * Create model and populate using the given data and field map.
     *
     * @param string modelClass The model class.
     * @param array data The data (keys are object property names)
     * @param array mapping The field mapping.
     * @return mixed The model instance.
     */
    protected function map2model($modelClass, $data, $mapping=null) {
        $model = ZMLoader::make($modelClass);

        foreach ($data as $key => $value) {
            $setter = 'set' . ucwords($key);
            if (method_exists($model, $setter)) {
                // specific method exists
                $model->$setter($value);
            } else {
                // use general purpose method
                $model->set($key, $value);
            }
        }

        return $model;
    }


    /**
     * Translate a given raw database row with the given mapping.
     *
     * @param array row The database row map.
     * @param array mapping The mapping (may be <code>null</code>).
     * @return array The mapped row.
     */
    protected function translateRow($row, $mapping) {
        if (null == $mapping) {
            return $row;
        }

        $mappedRow = array();
        foreach ($mapping as $field) {
            if (isset($row[$field['column']])) {
                $mappedRow[$field['field']] = $row[$field['column']];
            }
        }

        return $mappedRow;
    }


    /**
     * Parse mapping.
     *
     * @param array mapping The mapping table.
     */
    protected function parseMapping($mapping) {
        $tableInfo = array();
        foreach ($mapping as $field => $info) {
            $token = explode(':', $info);
            if (2 > count($token)) {
                ZMObject::backtrace('invalid table mapping: '.$info);
            }
            $isPrimary = 2 < count($token);
            $tableInfo[$field] = array('field' => $field, 'column' => $token[0], 'type' => $token[1], 'isPrimary' => $isPrimary);
        }
        return $tableInfo;
    }

}

?>
