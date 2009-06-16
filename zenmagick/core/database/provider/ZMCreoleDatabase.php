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
 * Implementation of the ZenMagick database layer using <em>Creole</em>.
 *
 * @see http://creole.phpdb.org/trac/
 * @author DerManoMann
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMCreoleDatabase extends ZMObject implements ZMDatabase {
    private $conn_;
    private $config_;
    private $queriesMap_;
    private $mapper_;


    /**
     * Create a new instance.
     *
     * @param array conf Configuration properties.
     */
    function __construct($conf) {
        parent::__construct();
        $drivers = array(
            'mysql' => 'MySQLConnection',
            'mysqli' => 'MySQLiConnection',
            'pgsql' => 'PgSQLConnection',
            'sqlite' => 'SQLiteConnection',
            'oracle' => 'OCI8Connection',
            'mssql' => 'MSSQLConnection',
            'odbc' => 'ODBCConnection'
        );
        if (!array_key_exists($conf['driver'], $drivers)) {
            throw ZMLoader::make('DatabaseException', 'invalid driver: ' . $conf['driver']);
        }
        // avoid creole dot notation as that does not work with the compressed version
        Creole::registerDriver($conf['driver'], $drivers[$conf['driver']]);
        // map some things that are named differently
        $conf['phptype'] = $conf['driver'];
        $conf['hostspec'] = $conf['host'];
        $this->config_ = $conf;
        $this->queriesMap_ = array();
        $this->conn_ = Creole::getConnection($conf);
        $this->mapper_ = ZMDbTableMapper::instance();
        if (null != $conf['initQuery']) {
            $this->conn_->executeQuery($conf['initQuery']);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
        $this->conn_ = null;
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
        $this->conn_->setAutoCommit(true);
    }

    /**
     * {@inheritDoc}
     */
    public function commit() {
        $this->conn_->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function rollback() {
        $this->conn_->rollback();
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        $stats = array();
        $time = 0;
        foreach ($this->queriesMap_ as $query) {
            $time += $query['time'];
        }
        $stats['time'] = $time;
        $stats['queries'] = count($this->queriesMap_);
        $stats['details'] = $this->queriesMap_;
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
    public function loadModel($table, $key, $modelClass, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

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
        $stmt = $this->prepareStatement($sql, array($keyName => $key), $mapping);

        $rs = $stmt->executeQuery();

        $results = array();
        while ($rs->next()) {
            $results[] = $this->rs2model($modelClass, $rs, $mapping);
        }

        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
        return 1 == count($results) ? $results[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function createModel($table, $model, $mapping=null) {
        if (null === $model) {
            return null;
        }

        $startTime = microtime();
        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        $sql = 'INSERT INTO '.$table.' SET';
        $firstSet = true;
        $beanModel = true;
        if (is_array($model)) {
            $properties = array_keys($model);
            $beanModel = false;
        } else {
            $properties = $model->getPropertyNames();
        }
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (in_array($field['property'], $properties) || (!$field['custom'] && $beanModel)) {
                if (!$field['auto']) {
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

        foreach ($mapping as $property => $field) {
            if ($field['auto']) {
                ZMBeanUtils::setAll($model, array($property => $newId));
            }
        }

        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data=array(), $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        $stmt = $this->prepareStatement($sql, $data, $mapping);
        $rows = $stmt->executeUpdate();
        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
        return $rows;
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($table, $model, $mapping=null) {
        if (null === $model) {
            return;
        }

        $startTime = microtime();
        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        $sql = 'UPDATE '.$table.' SET';
        $firstSet = true;
        $firstWhere = true;
        $where = ' WHERE ';
        $beanModel = true;
        if (is_array($model)) {
            $properties = array_keys($model);
            $beanModel = false;
        } else {
            $properties = $model->getPropertyNames();
        }
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (in_array($field['property'], $properties) || (!$field['custom'] && $beanModel)) {
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
            throw new ZMDatabaseException('missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $stmt->executeUpdate();
        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
    }

    /**
     * {@inheritDoc}
     */
    public function removeModel($table, $model, $mapping=null) {
        if (null === $model) {
            return null;
        }

        $startTime = microtime();
        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        $sql = 'DELETE FROM '.$table;
        $firstWhere = true;
        $where = ' WHERE ';
        $beanModel = true;
        if (is_array($model)) {
            $properties = array_keys($model);
            $beanModel = false;
        } else {
            $properties = $model->getPropertyNames();
        }
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (in_array($field['property'], $properties) || (!$field['custom'] && $beanModel)) {
                if ($field['key']) {
                    if (!$firstWhere) {
                        $where .= ' AND ';
                    }
                    $where .= $field['column'].' = :'.$field['property'];
                    $firstWhere = false;
                }
            }
        }
        if (7 > strlen($where)) {
            throw new ZMDatabaseException('missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $stmt->executeUpdate();
        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
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
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        $stmt = $this->prepareStatement($sql, $args, $mapping);
        $rs = $stmt->executeQuery();

        $results = array();
        while ($rs->next()) {
            $results[] = $this->rs2model($modelClass, $rs, $mapping);
        }

        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
        return $results;
    }

    /**
     * Create a prepared statement.
     *
     * @param string sql The initial SQL.
     * @param mixed args The data either as map or ZMObject instance.
     * @param array mapping The field mapping.
     * @return A <code>PreparedStatement</code> or null;
     */
    protected function prepareStatement($sql, $args, $mapping=null) {
        // make sure we are working on a map
        if (is_object($args)) {
            $args = ZMBeanUtils::obj2map($args, array_keys($mapping));
        }

        // find out the order of args
        // the sorting is done to avoid invalid matches in cases where one key is the prefix of another
        $argKeys = array_keys($args);
        rsort($argKeys);
        $regexp = ':'.implode($argKeys, '|:');
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
            $typeName = preg_replace('/[0-9]+#/', '', $name);
            $type = $mapping[$typeName]['type'];
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
                    //XXX: yeah, yeah
                    if (null === $value) {
                        $value = ZMDatabase::NULL_DATETIME;
                    }
                    $stmt->setTimestamp($index, $value);
                    break;
                case 'date':
                    //XXX: yeah, yeah
                    if (null === $value) {
                        $value = ZMDatabase::NULL_DATE;
                    }
                    $stmt->setDate($index, $value);
                    break;
                case 'blob':
                    $stmt->setBlob($index, $value);
                    break;
                default:
                    throw new ZMDatabaseException('unsupported data(prepare) type='.$type.' for name='.$name);
                }
                ++$index;
            }
        }

        return $stmt;
    }

    /**
     * Create model and populate using the given rs and field map.
     *
     * @param string modelClass The model class.
     * @param ResultSet rs A Creole result set.
     * @param array mapping The field mapping.
     * @return mixed The model instance or array (if modelClass is <code>null</code>).
     */
    protected function rs2model($modelClass, $rs, $mapping=null) {
        $row = $rs->getRow();
        if (null === $mapping || ZMDatabase::MODEL_RAW == $modelClass) {
            return $row;
        }

        // build typed data map
        $data = array();

        foreach ($mapping as $field => $info) {
            if (!array_key_exists($info['column'], $row)) {
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
                try {
                    // XXX creole will throw a fit as strtotime doesn't like ZMDatabase::NULL_DATETIME
                    $value = $rs->getTimestamp($info['column']);
                    if (ZMDatabase::NULL_DATETIME == $value) {
                        $value = null;
                    }
                } catch (SQLException $e) {
                    $value = null;
                }
                break;
            case 'date':
                try {
                    // XXX creole will throw a fit as strtotime doesn't like ZMDatabase::NULL_DATETIME
                    $value = $rs->getDate($info['column']);
                    if (ZMDatabase::NULL_DATE == $value) {
                        $value = null;
                    }
                } catch (SQLException $e) {
                    $value = null;
                }
                break;
            case 'blob':
                $blob = $rs->getBlob($info['column']);
                $value = null != $blob ? $blob->getContents() : null;
                break;
            default:
                throw new ZMDatabaseException('unsupported data(read) type='.$info['type'].' for field='.$field);
            }

            $data[$field] = $value;
        }

        // either data map or model instance
        return null == $modelClass ? $data : ZMBeanUtils::map2obj($modelClass, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData($table=null) {
        if (null !== $table) {
            $info = $this->conn_->getDatabaseInfo();
            $meta = null;
            foreach ($info->getTables() as $tbl) {
                if ($tbl->getName() == $table) {
                    $meta = array();
                    $primaryKey = $tbl->getPrimaryKey();
                    $primaryKeyName = (null !== $primaryKey ? $primaryKey->getName() : null);
                    foreach ($tbl->getColumns() as $col) {
                        $type = $col->getNativeType();
                        if (array_key_exists($type, ZMDbTableMapper::$NATIVE_TO_API_TYPEMAP)) {
                            $type = ZMDbTableMapper::$NATIVE_TO_API_TYPEMAP[$type];
                        } 
                        $name = $col->getName();
                        $meta[$name] = array(
                            'type' => $type,
                            'name' => $name,
                            'key' => $primaryKeyName == $name,
                            'autoIncrement' => $col->isAutoIncrement(),
                            'maxLen' => $col->getSize()
                        );
                    }
                    break;
                }
            }
            return $meta;
        } else {
            $info = $this->conn_->getDatabaseInfo();
            $tables = array();
            foreach ($info->getTables() as $tbl) {
                $tables[] = $tbl->getName();
            }
            return array('tables' => $tables);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getResource() {
        return $this->conn_;
    }

}

?>
