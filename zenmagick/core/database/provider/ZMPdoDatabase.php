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
 * Implementation of the ZenMagick database layer using <em>PDO</em>.
 *
 * <p>Support for nested transactions via <code>SAVEPOINT</code>s inspired by
 * http://www.kennynet.co.uk/2008/12/02/php-pdo-nested-transactions/.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMPdoDatabase extends ZMObject implements ZMDatabase {
    private $pdo_;
    private $config_;
    private $queriesMap_;
    private $mapper_;
    private static $SAVEPOINT_DRIVER = array('pgsql', 'mysql');
    private $savepointLevel_;
    


    /**
     * Create a new instance.
     *
     * <p>Supports the custom configuration setting <em>persistent</em> (<code>true</code> | <code>false</code>)</p>.
     *
     * @param array conf Configuration properties.
     */
    function __construct($conf) {
        parent::__construct();
        if (false !== ($colon = strpos($conf['host'], ':'))) {
            $conf['port'] = substr($conf['host'], $colon+1);
            $conf['host'] = substr($conf['host'], 0, $colon);
        }
        if (isset($conf['port']) && 'localhost' == $conf['host']) {
            // can't do port on localhost!
            $conf['host'] = '127.0.0.1';
        }

        // mysql and mysqli are the same for PDO
        $conf['driver'] = str_replace('mysqli', 'mysql', $conf['driver']);

        $url = $conf['driver'].':'.'host='.$conf['host'];
        if (isset($conf['port'])) {
            $url .= ';port='.$conf['port'];
        }
        $url .= ';dbname='.$conf['database'];
        $params = array();
        if (isset($conf['persistent']) && $conf['persistent']) {
            $params[PDO::ATTR_PERSISTENT] = true;
        }
        $this->config_ = $conf;
        $this->pdo_ = new PDO($url, $conf['username'], $conf['password'], $params);
        $this->pdo_->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->mapper_ = ZMDbTableMapper::instance();
        $this->queriesMap_ = array();
        $this->savepointLevel_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
        $this->pdo_ = null;
    }


    /**
     * Does this instance allow nested transactions?
     *
     * @return boolean <code>true</code> if nested transactions are supported.
     */
    protected function isNestedTransactions() {
        return in_array($this->config_['driver'], self::$SAVEPOINT_DRIVER);
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
        try {
            if (0 == $this->savepointLevel_ || !$this->isNestedTransactions()) {
                $this->pdo_->beginTransaction();
            } else {
                $this->pdo_->exec("SAVEPOINT LEVEL{$this->savepointLevel_}");
            }
            ++$this->savepointLevel_;
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function commit() {
        try {
            --$this->savepointLevel_;
            if (0 == $this->savepointLevel_ || !$this->isNestedTransactions()) {
                $this->pdo_->commit();
            } else {
                $this->pdo_->exec("RELEASE SAVEPOINT LEVEL{$this->savepointLevel_}");
            }
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rollback() {
        try {
            --$this->savepointLevel_;
            if (0 == $this->savepointLevel_ || !$this->isNestedTransactions()) {
                $this->pdo_->rollBack();
            } else {
                $this->pdo_->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
            }
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
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

        $keyName = ZMSettings::get('dbModelKeyName');
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

        try {
            $stmt->execute();
            $rows = $stmt->fetchAll();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        $results = array();
        foreach ($rows as $result) {
            if (null !== $mapping && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = ZMBeanUtils::map2obj($modelClass, $result);
            }
            $results[] = $result;
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

        try {
            $stmt = $this->prepareStatement($sql, $model, $mapping);
            $stmt->execute();
            $newId = $this->pdo_->lastInsertId();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
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

        try {
            $stmt = $this->prepareStatement($sql, $model, $mapping);
            $stmt->execute();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
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

        try {
            $stmt = $this->prepareStatement($sql, $model, $mapping);
            $stmt->execute();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data=array(), $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        try {
            $stmt = $this->prepareStatement($sql, $data, $mapping);
            $stmt->execute();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        $this->queriesMap_[] = array('time' => $this->getExecutionTime($startTime), 'sql' => $sql);
        return $rows;
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

        try {
            $stmt = $this->prepareStatement($sql, $args, $mapping);
            $stmt->execute();
            $rows = $stmt->fetchAll();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        $results = array();
        foreach ($rows as $result) {
            if (null !== $mapping && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = ZMBeanUtils::map2obj($modelClass, $result);
            }
            $results[] = $result;
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
        $PDO_INDEX_SEP = '__';
        static $typeMap = array(
          'integer' => PDO::PARAM_INT, 
          'string' => PDO::PARAM_STR,
          'boolean' => PDO::PARAM_BOOL,
          'date' => PDO::PARAM_STR,
          'time' => PDO::PARAM_INT,
          'blob' => PDO::PARAM_LOB,
          'datetime' => PDO::PARAM_STR,
          'float' => PDO::PARAM_STR
        );

        // make sure we are working on a map
        if (is_object($args)) {
            $args = ZMBeanUtils::obj2map($args, array_keys($mapping));
        }

        // PDO doesn't allow '#' in param names, so use something else
        $nargs = array();
        foreach (array_keys($args) as $name) {
            $nname = str_replace('#', $PDO_INDEX_SEP, $name);
            if ($name != $nname) {
                $sql = str_replace(':'.$name, ':'.$nname, $sql);
            }
            $nargs[$nname] = $args[$name];
        }
        $args = $nargs;

        foreach ($args as $name => $value) {
            if (is_array($value)) {
                $aargs = array();
                $index = 1;
                foreach ($value as $vv) {
                    $aargs[$index++.$PDO_INDEX_SEP.$name] = $vv;
                }
                // remove original
                unset($args[$name]);
                // add new split up values
                $args = array_merge($args, $aargs);
                // update SQL
                $sql = str_replace(':'.$name, ':'.implode(', :', array_keys($aargs)), $sql);
            }
        }

        // create statement
        $stmt = $this->pdo_->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($args as $name => $value) {
            $typeName = preg_replace('/[0-9]+'.$PDO_INDEX_SEP.'/', '', $name);
            if (false !== strpos($sql, ':'.$name) && array_key_exists($typeName, $mapping)) {
                // only bind if actually used
                $type = $mapping[$typeName]['type'];
                if (!array_key_exists($type, $typeMap)) {
                    throw new ZMDatabaseException('unsupported data(prepare) type='.$type.' for name='.$name);
                }
                //XXX: yeah, yeah
                if ($type == 'datetime' && null === $value) {
                    $value = ZMDatabase::NULL_DATETIME;
                } else if ($type == 'date' && null === $value) {
                    $value = ZMDatabase::NULL_DATE;
                }
                $stmt->bindValue(':'.$name, $value, $typeMap[$type]);
            }
        }

        return $stmt;
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
                if ('datetime' == $field['type']) {
                    if (ZMDatabase::NULL_DATETIME == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    }
                } else if ('date' == $field['type']) {
                    if (ZMDatabase::NULL_DATE == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    }
                }
            }
        }

        return $mappedRow;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData($table=null) {
        if (null !== $table) {
            $meta = array();
            try {
                $columns = $this->pdo_->query("SHOW COLUMNS FROM " . $table, PDO::FETCH_ASSOC);
            } catch (PDOException $pdoe) {
                ZMLogging::instance()->dump($pdoe);
                return null;
            }
            foreach($columns as $key => $col) {
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
            }
            return $meta;
        } else {
            $tables = array();
            $results = $this->pdo_->query("SHOW TABLES", PDO::FETCH_NUM);
            foreach ($results as $row) {
                $tables[] = $row[0];
            }
            return array('tables' => $tables);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getResource() {
        return $this->pdo_;
    }

}

?>
