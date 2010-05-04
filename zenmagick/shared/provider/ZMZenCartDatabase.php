<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package org.zenmagick.store.provider
 * @version $Id$
 */
class ZMZenCartDatabase extends ZMObject implements ZMDatabase {
    private static $typeMap = array('boolean' => 'integer', 'blob' => 'date', 'datetime' => 'date');
    private $db_;
    private $config_;
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
        parent::__construct();
        if ($conf['database'] == DB_DATABASE) {
        global $db;
            $this->db_ = $db;
        } else {
            $this->db_ = new queryFactory();
            $this->db_->connect($conf['host'], $conf['username'], $conf['password'], $conf['database'], USE_PCONNECT, false);
        }
        $this->config_ = $conf;
        $this->queriesCount = 0;
        $this->queriesTime = 0;
        $this->mapper = ZMDbTableMapper::instance();
        $this->debug = false;
        if (null != $conf['initQuery']) {
            $this->db_->Execute($conf['initQuery']);
        }
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
    public function getConfig() {
        return $this->config_;
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction() {
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
    public function loadModel($table, $key, $modelClass, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        $keyName = ZMSettings::get('database.model.keyName');
        if (null == $keyName) {
            // determine by looking at key and auto settings
            foreach ($mapping as $property => $field) {
                if ($field['auto'] && $field['key']) {
                    $keyName = $property;
                    break;
                }
            }
        }

        $field = $mapping[$keyName];
        $sql = 'SELECT * from '.$table.' WHERE '.$field['column'].' = :'.$keyName;
        $sql = $this->db_->bindVars($sql, ':'.$keyName, $key, $field['type']);

        if ($this->debug) {
            ZMLogging::instance()->log($sql, ZMLogging::TRACE);
        }
        $rs = $this->db_->Execute($sql);
        ++$this->queriesCount;

        $result = $rs->fields;
        if (null !== $mapping && ZMDatabase::MODEL_RAW != $modelClass) {
            $result = $this->translateRow($result, $mapping);
        }
        if (null != $modelClass && ZMDatabase::MODEL_RAW != $modelClass) {
            $result = ZMBeanUtils::map2obj($modelClass, $result);
        }

        $this->queriesTime += $this->getExecutionTime($startTime);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function createModel($table, $model, $mapping=null) {
        if (null === $model) {
            return null;
        }

        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // convert to array
        if (is_object($model)) {
            $modelData = ZMBeanUtils::obj2map($model, array_keys($mapping));
        } else {
            $modelData = $model;
        }

        $sql = 'INSERT INTO '.$table.' SET';
        $firstSet = true;
        $properties = array_keys($modelData);
        foreach ($mapping as $field) {
            if (in_array($field['property'], $properties)) {
                if (!$field['auto']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                    $firstSet = false;
                }
            }
        }

        $sql = $this->bindObject($sql, $modelData);
        if ($this->debug) {
            ZMLogging::instance()->log($sql, ZMLogging::TRACE);
        }
        $this->db_->Execute($sql);
        ++$this->queriesCount;

        foreach ($mapping as $property => $field) {
            if ($field['auto']) {
                $model = ZMBeanUtils::setAll($model, array($property => $this->db_->Insert_ID()));
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

        $mapping = $this->mapper->ensureMapping($mapping, $this);

        if (is_array($data)) {
            // find out the order of args
            // the sorting is done to avoid invalid matches in cases where one key is the prefix of another
            $argKeys = array_keys($data);
            rsort($argKeys);
            foreach ($argKeys as $name) {
                $value = $data[$name];
                // bind query parameter
                $typeName = preg_replace('/[0-9]+#/', '', $name);
                if (is_array($value)) {
                    $sql = $this->bindValueList($sql, ':'.$name, $value, self::getMappedType($mapping[$typeName]['type']));
                } else {
                    $sql = $this->db_->bindVars($sql, ':'.$name, $value, self::getMappedType($mapping[$typeName]['type']));
                }
            }
        } else if (is_object($data)) {
            $sql = $this->bindObject($sql, $data, $mapping);
        } else {
            throw new ZMDatabaseException('invalid data type');
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
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // convert to array
        if (is_object($model)) {
            $modelData = ZMBeanUtils::obj2map($model, array_keys($mapping));
        } else {
            $modelData = $model;
        }

        $sql = 'UPDATE '.$table.' SET';
        $firstSet = true;
        $firstWhere = true;
        $where = ' WHERE ';
        $properties = array_keys($modelData);
        foreach ($mapping as $field) {
            if (in_array($field['property'], $properties) && null !== $modelData[$field['property']]) {
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
        if (8 > strlen($where)) {
            throw new ZMDatabaseException('missing key');
        }
        $sql .= $where;

        $sql = $this->bindObject($sql, $modelData);
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
    public function removeModel($table, $model, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // convert to array
        if (is_object($model)) {
            $modelData = ZMBeanUtils::obj2map($model, array_keys($mapping));
        } else {
            $modelData = $model;
        }

        $sql = 'DELETE FROM '.$table;
        $firstWhere = true;
        $where = ' WHERE ';
        $properties = array_keys($modelData);
        foreach ($mapping as $field) {
            if (in_array($field['property'], $properties)) {
                if ($field['key']) {
                    if (!$firstWhere) {
                        $where .= ' AND ';
                    }
                    $where .= $field['column'].' = :'.$field['property'].';'.self::getMappedType($field['type']);
                    $firstWhere = false;
                }
            }
        }
        if (8 > strlen($where)) {
            throw new ZMDatabaseException('missing key');
        }
        $sql .= $where;

        $sql = $this->bindObject($sql, $modelData);
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
        $mapping = $this->mapper->ensureMapping($mapping, $this);

        // find out the order of args
        // the sorting is done to avoid invalid matches in cases where one key is the prefix of another
        $argKeys = array_keys($args);
        rsort($argKeys);
        foreach ($argKeys as $name) {
            $value = $args[$name];
            // bind query parameter
            $typeName = preg_replace('/[0-9]+#/', '', $name);
            if (is_array($value)) {
                $sql = $this->bindValueList($sql, ':'.$name, $value, self::getMappedType($mapping[$typeName]['type']));
            } else {
                $sql = $this->db_->bindVars($sql, ':'.$name, $value, self::getMappedType($mapping[$typeName]['type']));
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
            if (null !== $mapping && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = ZMBeanUtils::map2obj($modelClass, $result);
            }

            $results[] = $result;
            $rs->MoveNext();
        }

        $this->queriesTime += $this->getExecutionTime($startTime);
        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData($table=null) {
        if (null !== $table) {
            $results = $this->db_->Execute("SHOW COLUMNS FROM " . $table);
            $meta = array();
            while (!$results->EOF) {
                $col = $results->fields;
                $field = $col['Field'];
                preg_match('/([^(]*)(\([0-9]+\))?/', $col['Type'], $matches);
                $type = $matches[1];
                if (array_key_exists($type, ZMDbTableMapper::$NATIVE_TO_API_TYPEMAP)) {
                    $type = ZMDbTableMapper::$NATIVE_TO_API_TYPEMAP[$type];
                }
                $meta[$field] = array(
                    'type' => $type,
                    'name' => $field,
                    'key' => $col['Key'] == "PRI",
                    'autoIncrement' => false !== strpos($col['Extra'], 'auto_increment'),
                    'maxLen' => (3 == count($matches) ? (int)str_replace(array('(', ')'), '', $matches[2]) : null)
                );
                $results->MoveNext();
            }
            return $meta;
        } else {
            $results = $this->db_->Execute("SHOW TABLES");
            $tables = array();
            while (!$results->EOF) {
                $tables[] = array_pop($results->fields);
                $results->MoveNext();
            }
            return array('tables' => $tables);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getResource() {
        return $this->db_;
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
        $mappedFields = array();
        foreach ($mapping as $field) {
            if (array_key_exists($field['column'], $row)) {
                $mappedRow[$field['property']] = $row[$field['column']];
                $mappedFields[$field['column']] = $field['column'];
                if ('date' == $this->getMappedType($field['type'])) {
                    if (ZMDatabase::NULL_DATETIME == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    }
                }
            }
        }

        // handle unmapped fields as string...
        foreach ($row as $key => $value) {
            if (!array_key_exists($key, $mappedFields) && !array_key_exists($key, $mappedRow)) {
                $mappedRow[$key] = $value;
            }
        }

        return $mappedRow;
    }

    /**
     * Bind object to a given SQL query.
     *
     * <p>This is based on introspection/reflection on the given object and the available
     * <code>getXXX()</code> or <code>isXXX()</code> methods.</p>
     * <p>SQL label must follow the listed convenctions:</p>
     * <ul>
     *  <li>label start with the prefix '<code>:</code>'</li>
     *  <li>label match the objetcs <code>getXXX()</code> method excl the <code>get</code> prefix</li>
     *  <li>label are suffixed with the data type with a semicolon '<code>;</code>' as separator</li>
     * </ul>
     *
     * <p>Examples:</p>
     * <ul>
     *  <li><code>:firstName;string</code> - maps to the <code>getFirstName()</code> method; data type string</li>
     *  <li><code>:dob;date</code> - maps to the <code>getDob()</code> method; data type date</li>
     *  <li><code>:newsletterSubscriber;integer</code> - maps to the <code>isNewsletterSubscriber()</code> method; data type integer</li>
     * </ul>
     *
     * @param string sql The sql to work on.
     * @param mixed obj The data object instance.
     * @param array mapping Optional mapping; default is <code>null</code>.
     * @return string The updated SQL query.
     */
    protected function bindObject($sql, $obj, $mapping=null) {
        // prepare label
        preg_match_all('/:\w+;\w+/m', $sql, $matches);
        $labels = array();
        foreach ($matches[0] as $name) {
            $label = explode(';', $name);
            $labels[str_replace(':', '', $label[0])] = array($name, $label[1]);
        }
        // if no label found and mapping exists, use that as fallback
        if (0 == count($labels) && null != $mapping) {
            foreach ($mapping as $key => $info) {
                $labels[$key] = array(':'.$key, $info['type']);
            }
        }

        $data = ZMBeanUtils::obj2map($obj, array_keys($labels));

        foreach ($labels as $property => $info) {
            $value = null;
            if (array_key_exists($property, $data)) {
                $value = $data[$property];
            }
            
            // bind
            if ('date' == $info[1]) {
                // if not empty nothing, otherwise assume NULL
                if (empty($value)) {
                    $value = ZMDatabase::NULL_DATETIME;
                    $info[1] = 'date';
                }
            }
            $sql = $this->db_->bindVars($sql, $info[0], $value, $info[1]);
        }

        return $sql;
    }

    /**
     * Bind a list of values to a given SQL query.
     *
     * <p>Converts the values in the given array into a comma separated list of the specified type.</p>
     *
     * @param string sql The sql query to work on.
     * @param string bindName The name to bind the list to.
     * @param array values An array of values.
     * @param string type The value type; default is 'string'
     * @return string The sql with <code>$bindName</code> replaced with a properly formatted value list.
     */
    protected function bindValueList($sql, $bindName, $values, $type='string') {
        $fragment = '';
        foreach ($values as $value) {
            if ('' != $fragment) $fragment .= ', ';
            $fragment .= $this->db_->bindVars(":value", ":value", $value, $type);
        }

        return $this->db_->bindVars($sql, $bindName, $fragment, 'passthru');
    }

}
