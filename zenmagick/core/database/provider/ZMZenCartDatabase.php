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
 * @author DerManoMann
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMZenCartDatabase extends ZMObject implements ZMDatabase {
    private static $typeMap = array('boolean' => 'integer', 'blob' => 'date', 'datetime' => 'date');
    private $db_;
    private $queriesCount;
    private $queriesTime;
    private $mapper;
    private $debug;


    /**
     * Create a new instance.
     *
     * <p>Since this is just a wrapper around the existing global <code>$db</code>, the parameters
     * in <code>$conf</code> are ignored.</p>
     *
     * @param array conf Configuration properties.
     */
    function __construct($conf=null) {
    global $db;

        parent::__construct();
        $this->db_ = $db;
        $this->queriesCount = 0;
        $this->queriesTime = 0;
        $this->mapper = ZMDbTableMapper::instance();
        $this->debug = false;
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
    public function setAutoCommit($value) {
    }

    /**
     * {@inheritDoc}
     */
    public function commit() {
    }

    /**
     * {@inheritDoc}
     */
    public function rollback() {
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
    public static function getMappedType($type) {
        if (isset(self::$typeMap[$type])) {
            return self::$typeMap[$type];
        }
        return $type;
    }

    /**
     * {@inheritDoc}
     */
    public function createModel($table, $model, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table);

        $sql = 'INSERT INTO '.$table.' SET';
        $firstSet = true;
        $properties = array_flip($model->getPropertyNames());
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (!$field['custom'] || array_key_exists($field['property'], $properties)) {
                if (!$field['key']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                    $firstSet = false;
                }
            }
        }

        $sql = ZMDbUtils::bindObject($sql, $model, false);
        if ($this->debug) {
            ZMLogging::instance()->log($sql, ZMLogging::TRACE);
        }
        $this->db_->Execute($sql);
        ++$this->queriesCount;

        foreach ($mapping as $property => $field) {
            if ($field['auto']) {
                $newId = $this->db_->Insert_ID();
                $method = 'set'.$field['ucwp'];
                if (!method_exists($model, $method)) {
                    $model->set($property, $newId);
                } else {
                    $model->$method($newId);
                }
            }
        }

        $this->queriesTime += $this->getExecutionTime($startTime);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data=array(), $mapping=null) {
        $startTime = microtime();

        if (is_array($data)) {
            $mapping = $this->mapper->ensureMapping($mapping);
            // bind query parameter
            foreach ($data as $name => $value) {
                if (is_array($value)) {
                    $sql = ZMDbUtils::bindValueList($sql, ':'.$name, $value, self::getMappedType($mapping[$name]['type']));
                } else {
                    $sql = $this->db_->bindVars($sql, ':'.$name, $value, self::getMappedType($mapping[$name]['type']));
                }
            }
        } else if (is_object($data)) {
            $sql = ZMDbUtils::bindObject($sql, $data, false);
        } else {
            throw ZMLoader::make('ZMException', 'invalid data type');
        }
        if ($this->debug) {
            ZMLogging::instance()->log($sql, ZMLogging::TRACE);
        }
        $this->db_->Execute($sql);
        ++$this->queriesCount;
        $this->queriesTime += $this->getExecutionTime($startTime);
        return mysql_affected_rows($this->db_->link);
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($table, $model, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table);

        $sql = 'UPDATE '.$table.' SET';
        $firstSet = true;
        $firstWhere = true;
        $where = ' WHERE ';
        $properties = array_flip($model->getPropertyNames());
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (!$field['custom'] || array_key_exists($field['property'], $properties)) {
                if ($field['key']) {
                    if (!$firstWhere) {
                        $where .= ' AND ';
                    }
                    $where .= $field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                    $firstWhere = false;
                } else {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                    $firstSet = false;
                }
            }
        }
        if (7 > strlen($where)) {
            ZMLoggin::instance()->trace('missing key');
            die();
        }
        $sql .= $where;

        $sql = ZMDbUtils::bindObject($sql, $model, false);
        if ($this->debug) {
            ZMLogging::instance()->log($sql, ZMLogging::TRACE);
        }
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
        $mapping = $this->mapper->ensureMapping($mapping);

        // bind query parameter
        foreach ($args as $name => $value) {
            if (is_array($value)) {
                $sql = ZMDbUtils::bindValueList($sql, ':'.$name, $value, self::getMappedType($mapping[$name]['type']));
            } else {
                $sql = $this->db_->bindVars($sql, ':'.$name, $value, self::getMappedType($mapping[$name]['type']));
            }
        }

        $results = array();
        if ($this->debug) {
            ZMLogging::instance()->log($sql, ZMLogging::TRACE);
        }
        $rs = $this->db_->Execute($sql);
        ++$this->queriesCount;
        while (!$rs->EOF) {
            $result = $rs->fields;
            if (null !== $mapping && ZM_DB_MODEL_RAW != $modelClass) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass && ZM_DB_MODEL_RAW != $modelClass) {
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
            if (array_key_exists($field['column'], $row)) {
                $mappedRow[$field['property']] = $row[$field['column']];
                if ('date' == $this->getMappedType($field['type'])) {
                    if (ZM_DB_NULL_DATETIME == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    }
                }
            }
        }

        return $mappedRow;
    }

}

?>
