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
 * Implementation of the ZenMagick database layer using <em>Creole</em>.
 *
 * @see http://creole.phpdb.org/trac/
 * @author mano
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMCreoleDatabase extends ZMObject implements ZMDatabase {
    private $conn_;
    private $queriesCount;
    private $queriesTime;
    private $queriesMap = array();
    private $mapper;


    /**
     * Create a new instance.
     */
    function __construct() {
        parent::__construct();
        // avoid creole dot notation as that does not work here
        Creole::registerDriver('mysql', 'MySQLConnection');
        $dsn = array('phptype' => 'mysql',
                     'hostspec' => DB_SERVER,
                     'username' => DB_SERVER_USERNAME,
                     'password' => DB_SERVER_PASSWORD,
                     'database' => DB_DATABASE);
        $this->conn_ = Creole::getConnection($dsn);
        $this->mapper = ZMDbTableMapper::instance();
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
        $stats['details'] = $this->queriesMap;
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
            if (!$field['custom'] || isset($properties[$field['property']])) {
                if (!$field['key']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'];
                    $firstSet = false;
                }
            }
        }

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $idgen = $this->conn_->getIdGenerator();
        $newId = null;
        //XXX: add support for SEQUENCE?
        if($idgen->isBeforeInsert()) {
            $newId = $idgen->getId();
            $stmt->executeUpdate();
        } else { // isAfterInsert()
            $stmt->executeUpdate();
            $newId = $idgen->getId();
        }
        ++$this->queriesCount;

        foreach ($mapping as $property => $field) {
            if ($field['auto']) {
                $method = 'set'.ucwords($property);
                if (!method_exists($model, $method)) {
                    ZMObject::backtrace('missing auto key setter ' . $method);
                }
                call_user_func(array($model, $method), $newId);
            }
        }

        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data, $mapping) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping($mapping);

        $stmt = $this->prepareStatement($sql, $data, $mapping);
        $rows = $stmt->executeUpdate();
        ++$this->queriesCount;
        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $rows;
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
            if (!$field['custom'] || isset($properties[$field['property']])) {
                if ($field['key']) {
                    if (!$firstWhere) {
                        $where .= ' AND ';
                    }
                    $where .= $field['column'].' = :'.$field['property'];
                    $firstWhere = false;
                } else {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'];
                    $firstSet = false;
                }
            }
        }
        if (7 > strlen($where)) {
            ZMObject::backtrace('missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $stmt->executeUpdate();
        ++$this->queriesCount;
        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
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

        $stmt = $this->prepareStatement($sql, $args, $mapping);
        $rs = $stmt->executeQuery();
        ++$this->queriesCount;

        $results = array();
        while ($rs->next()) {
            $results[] = self::rs2model($modelClass, $rs, $mapping);
        }

        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $results;
    }

    /**
     * Create a prepared statement.
     *
     * @param string sql The initial SQL.
     * @param mixed args The data either as map or ZMModel instance.
     * @param array mapping The field mapping.
     * @return A <code>PreparedStatement</code> or null;
     */
    protected function prepareStatement($sql, $args, $mapping=null) {
        // make sure we are working on a map
        if (is_object($args)) {
            $args = $this->model2map($args, $mapping);
        }

        // find out the order of args
        $regexp = ':'.implode(array_keys($args), '|:');
        preg_match_all('/'.$regexp.'/', $sql, $argOrder);
        $argOrder = $argOrder[0];
        // modify SQL replacing :key syntax with ?
        foreach (explode('|', $regexp) as $ii => $key) {
            $name = substr($key, 1);
            if (!empty($name)) {
                $pl = '?';
                if (isset($args[$name]) && is_array($args[$name])) {
                    // expand placeholder
                    for ($ii=1; $ii < count($args[$name]); ++$ii) {
                        $pl .= ',?';
                    }
                }
                $sql = str_replace($key, $pl, $sql);
            }
        }

        // create statement
        $stmt = $this->conn_->prepareStatement($sql);
        $index = 1;
        // set values by index
        foreach ($argOrder as $name) {
            $name = substr($name, 1);
            $type = $mapping[$name]['type'];
            $values = $args[$name];
            if (!is_array($values)) {
                // treat all values as value arrays
                $values = array($values);
            }
            foreach ($values as $value) {
                switch ($type) {
                case 'integer':
                    $stmt->setInt($index, $value);
                    break;
                case 'boolean':
                    $stmt->setBoolean($index, $value);
                    break;
                case 'string':
                    $stmt->setString($index, $value);
                    break;
                case 'float':
                    $stmt->setFloat($index, $value);
                    break;
                case 'datetime':
                    $stmt->setTimestamp($index, $value);
                    break;
                case 'date':
                    $stmt->setDate($index, $value);
                    break;
                case 'blob':
                    $stmt->setBlob($index, $value);
                    break;
                default:
                    ZMObject::backtrace('unsupported data(prepare) type='.$type.' for name='.$name);
                    break;
                }
                ++$index;
            }
        }

        return $stmt;
    }

    /**
     * Create a hash map based on the properties of the given object.
     *
     * @param obj The object.
     * @param array mapping The mapping.
     * @return array A hash map of all extracted object properties.
     * @todo cleanup all bean related code
     */
    protected function model2map($obj, $mapping) {
        $prefixList = array('get', 'is', 'has');

        $map = array();
        foreach ($mapping as $name => $info) {
            foreach ($prefixList as $prefix) {
                $getter = $prefix . $info['ucwp'];
                if (method_exists($obj, $getter)) {
                    $map[$name] = call_user_func(array($obj, $getter));
                    break;
                }
            }
        }

        if ($obj instanceof ZMModel) {
            foreach ($obj->getPropertyNames() as $name) {
                $map[$name] = $obj->__get($name);
            }
        }

        return $map;
    }

    /**
     * Create model and populate using the given rs and field map.
     *
     * @param string modelClass The model class.
     * @param ResultSet rs A Creole result set.
     * @param array mapping The field mapping.
     * @return mixed The model instance or array (if modelClass is <code>null</code>).
     */
    protected static function rs2model($modelClass, $rs, $mapping=null) {
        if (null != $modelClass) {
            $model = ZMLoader::make($modelClass);
        } else {
            $model = array();
        }

        $row = $rs->getRow();
        if (null === $mapping) {
            return $row;
        }
        foreach ($mapping as $field => $info) {
            if (!isset($row[$info['column']])) {
                // field not in result set, so ignore
                continue;
            }

            switch ($info['type']) {
            case 'integer':
                $value = $rs->getInt($info['column']);
                break;
            case 'boolean':
                $value = $rs->getBoolean($info['column']);
                break;
            case 'string':
                $value = $rs->getString($info['column']);
                break;
            case 'float':
                $value = $rs->getFloat($info['column']);
                break;
            case 'datetime':
                $value = $rs->getTimestamp($info['column']);
                break;
            case 'date':
                $value = $rs->getDate($info['column'], 'Y-m-d');
                break;
            case 'blob':
                $blob = $rs->getBlob($info['column']);
                $value = $blob->getContents();
                break;
            default:
                ZMObject::backtrace('unsupported data(read) type='.$info['type'].' for field='.$field);
                break;
            }

            if (null != $modelClass) {
                $setter = 'set'.$info['ucwp'];
                if (method_exists($model, $setter)) {
                    // specific method exists
                    call_user_func(array($model, $setter), $value);
                } else {
                    // use general purpose method
                    $model->__set($field, $value);
                }
            } else {
                $model[$field] = $value;
            }
        }

        return $model;
    }

}

?>
