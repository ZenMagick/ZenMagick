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
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMZenCartDatabase extends ZMObject implements ZMDatabase {
    private static $typeMap = array('boolean' => 'integer', 'blob' => 'date');
    private $db_;
    private $queriesCount;
    private $queriesTime;


    /**
     * Create a new instance.
     */
    function __construct() {
    global $db;

        parent::__construct();
        $this->db_ = $db;
        $this->queriesCount = 0;
        $this->queriesTime = 0;
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
    public function getStats() {
        $stats = array();
        $stats['time'] = $this->queriesTime;
        $stats['queries'] = $this->queriesCount;
        return $stats;
    }

    /**
     * Get the elapsed time since <code>$start</code>.
     *
     * @param string start The starting time.
     * @return long The time in milliseconds.
     */
    protected function getExecutionTime($start) {
        $start = explode (' ', $start);
        $end = explode (' ', microtime());
        return $end[1]+$end[0]-$start[1]-$start[0];
    }

    /**
     * Optional mappings.
     *
     * <p>Allows to use types not supported bb zen-cart, for example <em>boolean</em>.</p>
     *
     * @param string type The type.
     * @return string A valid zen-cart data type.
     */
    private function getMappedType($type) {
        if (isset(self::$typeMap[$type])) {
            return self::$typeMap[$type];
        }
        return $type;
    }

    /**
     * {@inheritDoc}
     */
    public function createModel($table, $model, $mapping) {
        $startTime = microtime();
        $mapping = ZMDbUtils::parseMapping($mapping);

        $sql = 'INSERT INTO '.$table.' SET';
        $firstSet = true;
        foreach ($mapping as $field) {
            if (!$field['readonly'] && !$field['primary']) {
                if (!$firstSet) {
                    $sql .= ',';
                }
                $sql .= ' '.$field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                $firstSet = false;
            }
        }

        $sql = ZMDbUtils::bindObject($sql, $model, false);
        ZMObject::log($sql, ZM_LOG_TRACE);
        $this->db_->Execute($sql);
        ++$this->queriesCount;

        foreach ($mapping as $property => $field) {
            if ($field['primary']) {
                $newId = $this->db_->Insert_ID();
                $method = 'set'.ucwords($property);
                if (!method_exists($model, $method)) {
                    ZMObject::backtrace('missing primary key setter ' . $method);
                }
                $model->$method($newId);
            }
        }

        $this->queriesTime += $this->getExecutionTime($startTime);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data, $mapping) {
        $startTime = microtime();
        if (is_array($data)) {
            $mapping = ZMDbUtils::parseMapping($mapping);
            // bind query parameter
            foreach ($data as $name => $value) {
                $sql = $this->db_->bindVars($sql, ':'.$name, $value, self::getMappedType($mapping[$name]['type']));
            }
        } else if (is_object($data)) {
            $sql = ZMDbUtils::bindObject($sql, $data, false);
        } else {
            ZMObject::backtrace('invalid data type');
        }
        ZMObject::log($sql, ZM_LOG_TRACE);
        $this->db_->Execute($sql);
        ++$this->queriesCount;
        $this->queriesTime += $this->getExecutionTime($startTime);
        return mysql_affected_rows($this->db_->link);
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($table, $model, $mapping) {
        $startTime = microtime();
        $mapping = ZMDbUtils::parseMapping($mapping);

        $sql = 'UPDATE '.$table.' SET';
        $firstSet = true;
        $firstWhere = true;
        $where = ' WHERE ';
        foreach ($mapping as $field) {
            if ($field['key'] || $field['primary']) {
                if (!$firstWhere) {
                    $where .= ' AND ';
                }
                $where .= $field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                $firstWhere = false;
            } else {
                if (!$field['readonly']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                    $firstSet = false;
                }
            }
        }
        if (7 > strlen($where)) {
            ZMObject::backtrace('missing key');
        }
        $sql .= $where;

        $sql = ZMDbUtils::bindObject($sql, $model, false);
        ZMObject::log($sql, ZM_LOG_TRACE);
        $this->db_->Execute($sql);
        ++$this->queriesCount;
        $this->queriesTime += $this->getExecutionTime($startTime);
    }

    /**
     * {@inheritDoc}
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null) {
        $results = $this->query($sql, $args, $mapping, $modelClass);
        return 0 < count($results) ? $results[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null) {
        $startTime = microtime();
        $mapping = ZMDbUtils::parseMapping($mapping);

        // bind query parameter
        foreach ($args as $name => $value) {
            $sql = $this->db_->bindVars($sql, ':'.$name, $value, self::getMappedType($mapping[$name]['type']));
        }

        $results = array();
        ZMObject::log($sql, ZM_LOG_TRACE);
        $rs = $this->db_->Execute($sql);
        ++$this->queriesCount;
        while (!$rs->EOF) {
            $result = $rs->fields;
            if (null != $mapping) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass) {
                $result = ZMDbUtils::map2model($modelClass, $result, $mapping);
            }

            $results[] = $result;
            $rs->MoveNext();
        }

        $this->queriesTime += $this->getExecutionTime($startTime);
        return $results;
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
                $mappedRow[$field['property']] = $row[$field['column']];
            }
        }

        return $mappedRow;
    }

}

?>
