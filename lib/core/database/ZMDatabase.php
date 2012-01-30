<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

/**
 * ZenMagick database abstractation.
 *
 * <p>A generic, lightweight database layer.</p>
 *
 * <p>Support for the following array keys is required:</p>
 * <dl>
 *  <dt>driver</dt>
 *  <dd>The database type; typical values would be: <em>pdo_mysql</em>, <em>pdo_sqlite</em> or <em>pdo_pgsql</em>.</dd>
 *  <dt>host</dt>
 *  <dd>The database host (and port).</dd>
 *  <dt>port</dt>
 *  <dd>The database port.</dd>
 *  <dt>user</dt>
 *  <dd>The database username.</dd>
 *  <dt>password</dt>
 *  <dd>The password for the database user.</dd>
 *  <dt>dbname</dt>
 *  <dd>The name of the database to connect to.</dd>
 *  <dt>prefix</dt>
 *  <dd>Optional table prefix.</dd>
 * </dl>
 *
 * <p>The data will be bound to the SQL using the configured table mappings.</p>
 *
 * <p>It is also possible to generate table mappings on the fly. In that case no name translation will be done.</p>
 *
 * <p>Syntax for parameters in SQL is: <em>:{[0-9]+#}[propertyName]</em>. The numeric prefix (for example
 * <code>:3#categoryId</code>) is optional and only required if multiple values of a column are used in a single SQL
 * statement.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.database
 */
class ZMDatabase extends ZMObject {
    /** If used as modelClass parameter, the raw SQL data will be returned (no mapping, etc). */
    const MODEL_RAW = '@raw';

    /** Internal date format. */
    const DATE_FORMAT = 'Y-m-d';

    /** Internal date-time format. */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /** NULL date. */
    const NULL_DATE = '0001-01-01';

    /** NULL datetime. */
    const NULL_DATETIME = '0001-01-01 00:00:00';

    protected $pdo_;
    protected $mapper_;

    /**
     * Create a new instance.
     *
     * @param array params Configuration properties.
     */
    public function __construct(array $params) {
        parent::__construct();
        $this->mapper_ = ZMDbTableMapper::instance();

        $pdo = Doctrine\DBAL\DriverManager::getConnection($params);

        // @todo don't tie logging to the pageStats plugin
        // @todo look at doctrine.dbal.logging (boolean) and doctrine.dbal.logger_class
        $pdo->getConfiguration()->setSQLLogger(new Doctrine\DBAL\Logging\DebugStack);

        $pdo->getEventManager()->addEventSubscriber(new Doctrine\DBAL\Event\Listeners\MysqlSessionInit($params['charset'], $params['collation']));

        // @todo enum: remove or add doctrine mapping type
        $pdo->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        // alias boolean to boolean so ZMDbTableMapper maps continue to work
        $pdo->getDatabasePlatform()->registerDoctrineTypeMapping('boolean', 'boolean');

        // @todo ask DBAL if the driver/db type supports nested transactions
        $pdo->setNestTransactionsWithSavepoints(true);
        $this->pdo_ = $pdo;
    }

   /**
     * Gets the prefix used by this connection.
     *
     * @return string
     */
    public function getPrefix() {
		$params = $this->getParams();
		return isset($params['prefix']) ? $params['prefix'] : null;
        //return isset($this->_params['prefix']) ? $this->_params['prefix'] : null;
    }

    /**
     * Get the configuration parameters for this instance.
     *
     * @return array Configuration settings as known by dbal.
     */
    public function getParams() {
        return $this->pdo_->getParams();
    }

    /**
     * Start a transaction.
     *
     * <p>If the database provider (and database driver) allow, nested transaction are possible.</p>
     *
     * @throws ZMDatabaseException
     */
    public function beginTransaction() {
        try {
            $this->pdo_->beginTransaction();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * Commits statements in a transaction.
     *
     * @throws ZMDatabaseException
     */
    public function commit() {
        try {
            $this->pdo_->commit();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * Rollback changes in a transaction.
     *
     * @throws ZMDatabaseException
     */
    public function rollback() {
        try {
            $this->pdo_->rollback();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * Get some stats about database usage.
     *
     * @return array A map with statistical data.
     */
    public function getStats() {
        $stats = array();
        $time = 0;
        $logger = $this->pdo_->getConfiguration()->getSQLLogger();
        foreach ($logger->queries as $key => $query) {
            $logger->queries[$key]['time'] = $query['executionMS'];
            $time += $query['executionMS'];
        }
        $stats['time'] = $time;
        $stats['queries'] = count($logger->queries);
        $stats['details'] = $logger->queries;
        return $stats;
    }

    /**
     * Load a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed key The primary key.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @return mixed The model with the updated primary key.
     * @throws ZMDatabaseException
     */
    public function loadModel($table, $key, $modelClass, $mapping = null) {
        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // determine by looking at key and auto settings
        foreach ($mapping as $property => $field) {
            if ($field['auto'] && $field['key']) {
                $keyName = $property;
                break;
            }
        }

        $field = $mapping[$keyName];
        $sql = 'SELECT * from '.$table.' WHERE '.$field['column'].' = :'.$keyName;
        $stmt = $this->prepareStatement($sql, array($keyName => $key), $mapping);


        try {
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $stmt->closeCursor();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
        $results = array();
        foreach ($rows as $result) {
            if (null !== $mapping && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = Beans::map2obj($modelClass, $result);
            }
            $results[] = $result;
        }

        return 1 == count($results) ? $results[0] : null;
    }

    /**
     * Create a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @return mixed The model with the updated primary key.
     * @throws ZMDatabaseException
     */
    public function createModel($table, $model, $mapping = null) {
        if (null === $model) {
            return null;
        }

        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // convert to array
        if (is_object($model)) {
            $modelData = Beans::obj2map($model, array_keys($mapping));
        } else {
            $modelData = $model;
        }
        $sql = 'INSERT INTO '.$table.' SET';
        $firstSet = true;
        $properties = array_keys($modelData);
        foreach ($mapping as $field) {
            if (!in_array($field['property'], $properties) && null != $mapping[$field['property']]['default']) {
                // use default
                $modelData[$field['property']] = $mapping[$field['property']]['default'];
                // add to properties list
                $properties[] = $field['property'];
            }
            if (in_array($field['property'], $properties)) {
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
            $stmt = $this->prepareStatement($sql, $modelData, $mapping);
            $stmt->execute();
            $newId = $this->pdo_->lastInsertId();
            $stmt->closeCursor();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        foreach ($mapping as $property => $field) {
            if ($field['auto']) {
                $model = Beans::setAll($model, array($property => $newId));
            }
        }

        return $model;
    }

    /**
     * Remove a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @throws ZMDatabaseException
     */
    public function removeModel($table, $model, $mapping = null) {
        if (null === $model) {
            return null;
        }

        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // convert to array
        if (is_object($model)) {
            $modelData = Beans::obj2map($model, array_keys($mapping));
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
                    $where .= $field['column'].' = :'.$field['property'];
                    $firstWhere = false;
                }
            }
        }
        if (8 > strlen($where)) {
            throw new ZMDatabaseException('missing key');
        }
        $sql .= $where;

        try {
            $stmt = $this->prepareStatement($sql, $modelData, $mapping);
            $stmt->execute();
            $stmt->closeCursor();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * Update a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @throws ZMDatabaseException
     */
    public function updateModel($table, $model, $mapping = null) {
        if (null === $model) {
            return;
        }

        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        // convert to array
        if (is_object($model)) {
            $modelData = Beans::obj2map($model, array_keys($mapping));
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
        if (8 > strlen($where)) {
            throw new ZMDatabaseException('missing key');
        }
        $sql .= $where;

        try {
            $stmt = $this->prepareStatement($sql, $model, $mapping);
            $stmt->execute();
            $stmt->closeCursor();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }
    }

    /**
     * Update using the provided SQL and data and model.
     *
     * @param string query The update sql query.
     * @param mixed data A model instance or array; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @return int affected rows
     * @throws ZMDatabaseException
     */
    public function update($query, $params = array(), $mapping = null) {
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        // convert to array
        if (is_object($params)) {
            $params = Beans::obj2map($params, array_keys($mapping));
        }
        try {
            $stmt = $this->prepareStatement($query, $params, $mapping);
            $stmt->execute();
            $rows = $stmt->rowCount();
            $stmt->closeCursor();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        return $rows;
    }

    /**
     * Execute a query expecting a single result.
     *
     * <p><code>$modelClass</code> may be set to the magic value of <code>ZMDatabase::MODEL_RAW</code> to force
     * returning the raw data without applying any mappings or conversions.</p>
     *
     * @param string sql The query.
     * @param array params Optional query parameters; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return mixed The (expected) single result or <code>null</code>
     * @throws ZMDatabaseException
     */
    public function querySingle($sql, array $params = array(), $mapping = null, $modelClass = null) {
        $results = $this->query($sql, $params, $mapping, $modelClass);
        return 0 < count($results) ? $results[0] : null;
    }

    /**
     * Execute a query.
     *
     * <p>If <code>$resultClass</code> is <code>null</code>, the returned
     * list will contain a map of <em>columns</em> =&gt; <em>value</em> for each selected row.</p>
     *
     * <p><code>$modelClass</code> may be set to the magic value of <code>ZMDatabase::MODEL_RAW</code> to force
     * returning the raw data without applying any mappings or conversions.</p>
     *
     * @param string sql The query.
     * @param array params Optional query parameters; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return array List of populated objects of class <code>$resultClass</code> or map if <em>modelClass</em> is <code>null</code>.
     * @throws ZMDatabaseException
     */
    public function query($sql, array $params = array(), $mapping = null, $modelClass = null) {
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        try {
            $stmt = $this->prepareStatement($sql, $params, $mapping);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        $results = array();
        foreach ($rows as $result) {
            if (ZMDatabase::MODEL_RAW == $modelClass) continue;
            if (null !== $mapping) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass) {
                $result = Beans::map2obj($modelClass, $result);
            }
            $results[] = $result;
        }

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
    protected function prepareStatement($sql, $params, $mapping = null) {
        $PDO_INDEX_SEP = '__';

        // make sure we are working on a map
        if (is_object($params)) {
            $params = Beans::obj2map($params, array_keys($mapping));
        }

        // PDO doesn't allow '#' in param names, so use something else
        $nargs = array();
        foreach (array_keys($params) as $name) {
            $nname = str_replace('#', $PDO_INDEX_SEP, $name);
            if ($name != $nname) {
                $sql = str_replace(':'.$name, ':'.$nname, $sql);
            }
            $nargs[$nname] = $params[$name];
        }
        $params = $nargs;

        // handle array args
        foreach ($params as $name => $value) {
            if (is_array($value)) {
                $aargs = array();
                $index = 1;
                foreach ($value as $vv) {
                    $aargs[$index++.$PDO_INDEX_SEP.$name] = $vv;
                }
                // remove original
                unset($params[$name]);
                // add new split up values
                $params = array_merge($params, $aargs);
                // update SQL
                $sql = str_replace(':'.$name, ':'.implode(', :', array_keys($aargs)), $sql);
            }
        }

        // create statement
        $stmt = $this->pdo_->prepare($sql);
        foreach ($params as $name => $value) {
            $typeName = preg_replace('/[0-9]+'.$PDO_INDEX_SEP.'/', '', $name);
            if (false !== strpos($sql, ':'.$name) && array_key_exists($typeName, $mapping)) {
                // only bind if actually used
                $type = $mapping[$typeName]['type'];

                // @todo do we really want to keep ZMDatabase::NULL_DATE* for native ZM code/plugins or keep it at all?
                if ('datetime' == $type && null == $value) {
                    $value = ZMDatabase::NULL_DATETIME;
                }
                if ('date' == $type && null == $value) {
                   $value = ZMDatabase::NULL_DATE;
                }
                if(('date' == 'type' || 'datetime' == $type) && is_string($value)) {
                    $value = new DateTime($value);
                }

                try {
                    $dbalType = $this->pdo_->getDatabasePlatform()->getDoctrineTypeMapping($type);
                } catch(\Doctrine\DBAL\DBALException $e) {
                    throw new ZMDatabaseException('unsupported data(prepare) type='.$type.' for name='.$name);
                }
                $x = $stmt->bindValue(':'.$name, $value, $dbalType);
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
        $mappedFields = array();
        foreach ($mapping as $field) {
            if (array_key_exists($field['column'], $row)) {
                $mappedRow[$field['property']] = $row[$field['column']];
                $mappedFields[$field['column']] = $field['column'];
                if ('datetime' == $field['type']) {
                    if (ZMDatabase::NULL_DATETIME == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    } else {
                        $mappedRow[$field['property']] = new DateTime($mappedRow[$field['property']]);
                    }
                } else if ('date' == $field['type']) {
                    if (ZMDatabase::NULL_DATE == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    } else {
                        $mappedRow[$field['property']] = new DateTime($mappedRow[$field['property']]);
                    }
                } else if ('boolean' == $field['type']) {
                    $mappedRow[$field['property']] = Toolbox::asBoolean($row[$field['column']]);
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
     * Get meta data.
     *
     * <p>Meta data is available for an individual table.</p>
     *
     * <p>The following table information will be returned:</p>
     * <dl>
     *  <dt>type</dt>
     *  <dd>The data type. This will be a data type as supported by the <code>ZMDatabase</code> API.</dd>
     *  <dt>name</dt>
     *  <dd>The (case sensitive) column name.</dd>
     *  <dt>key</dt>
     *  <dd>A boolean indicating a primary key</dd>
     *  <dt>auto</dt>
     *  <dd>A boolean flag indication an auto increment column</dd>
     *  <dt>maxLen</dt>
     *  <dd>The max. field length; this value is context specific.</dd>
     * </dl>
     *
     * @param string table table to get metadata from
     * @return array Context dependent meta data.
     * @throws ZMDatabaseException
     */
    public function getMetaData($table) {
        $params = $this->getParams();
        $sm = $this->getSchemaManager();
        if (!empty($params['prefix']) && 0 !== strpos($table, $params['prefix'])) {
            $table = $params['prefix'].$table;
        }

        $meta = array();

        try {
            $tableDetails = $sm->listTableDetails($table);
        } catch(Doctrine\DBAL\Schema\SchemaException $pdoe) {
            throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
        }

        // TODO: yes we have a table without a primary key :(
        $primaryKey = $tableDetails->getPrimaryKey();
        $keys = is_object($primaryKey) ? $primaryKey->getColumns() : array();

        foreach($tableDetails->getColumns() as $column) {
            $meta[$column->getName()] = array(
                'type' => $column->getType()->getName(),
                'name' => $column->getName(),
                'key' => in_array($column->getName(), $keys),
                'auto' => $column->getAutoincrement(),
                'maxLen' => $column->getLength(),/* TODO doesn't work for integers*/
                'default' => $column->getDefault()
             );
        }
        return $meta;
    }

    /**
     * Get underlying PDO instance.

     * @return object
     */
    public function getResource() {
        return $this->pdo_;
    }

    // just like Doctrine\DBAL\Connection
    public function getSchemaManager() { return $this->pdo_->getSchemaManager(); }
}
