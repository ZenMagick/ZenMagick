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
    protected $config_;
    protected $mapper_;
    protected static $SAVEPOINT_DRIVER = array('pdo_pgsql', 'pdo_mysql');
    protected $evm_;
    protected $dbalConfig_;
    protected $ormConfig_;
    protected $em_;

    /**
     * Create a new instance.
     *
     * <p>Supports the custom configuration setting <em>persistent</em> (<code>true</code> | <code>false</code>)</p>.
     *
     * @param array conf Configuration properties.
     */
    function __construct($conf) {
        parent::__construct();
        $this->config_ = $this->resolveConf($conf);
        $this->mapper_ = ZMDbTableMapper::instance();
        $this->evm_ = new Doctrine\Common\EventManager();

        // @todo don't tie logging to the pageStats plugin
        // @todo look at doctrine.dbal.logging (boolean) and doctrine.dbal.logger_class
        $dbalConfig = new Doctrine\DBAL\Configuration;
        $dbalConfig->setSQLLogger(new Doctrine\DBAL\Logging\DebugStack);
        $this->dbalConfig_ = $dbalConfig;

        if (!Doctrine\DBAL\Types\Type::hasType('blob')) {
            Doctrine\DBAL\Types\Type::addType('blob', '\zenmagick\base\database\doctrine\types\Blob');
            Doctrine\DBAL\Types\Type::addType('mediumblob', '\zenmagick\base\database\doctrine\types\MediumBlob');
        }

        $this->ensureResource($conf);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
        $this->pdo_ = null;
    }

    /**
     *  Fix up db parameters that come in a variety of formats across the codebase
     *
     *  @todo remove?
     *  @todo where should these db parameters be validated/munged?
     *  @param $conf mixed
     */
    public function resolveConf($conf = null) {
        // @todo get defaults here? or elsewhere?
        $rename = array('database' => 'dbname', 'username' => 'user', 'socket' => 'unix_socket');
        foreach ($rename as $old => $new) {
            if (array_key_exists($old, $conf) && !empty($conf[$old])) {
                $conf[$new] = $conf[$old];
                unset($conf[$old]);
            }
        }
        if (isset($conf['driver']) && (false !== strpos('pdo_', $conf['driver']))) {
            $conf['driver'] = 'pdo_' . str_replace('mysqli', 'mysql', $conf['driver']);
        }

        if (!isset($conf['host']) || empty($conf['host'])) $conf['host'] = 'localhost';
        if (false !== ($colon = strpos($conf['host'], ':'))) {
            $conf['port'] = substr($conf['host'], $colon+1);
            $conf['host'] = substr($conf['host'], 0, $colon);
        }

        if (!isset($conf['prefix']) || is_null($conf['prefix'])) $conf['prefix'] = '';

        if (isset($conf['persistent']) && $conf['persistent']) {
            $conf['driverOptions'][PDO::ATTR_PERSISTENT] = true;
        }
        return $conf;
    }

    /**
     * Create native resource.
     *
     * @param array conf Optional config; if <code>null</code> the global config will be used; default is <code>null</code>.
     */
    protected function ensureResource($conf=null) {
        if (null == $this->pdo_){
            $conf = null !== $conf ? $this->resolveConf($conf) : $this->config_;

            // this is driver specific, but we'll sure move to the doctrine bundle before being able to switch drivers....
            $pdo = Doctrine\DBAL\DriverManager::getConnection($conf, $this->dbalConfig_, $this->evm_);
            $this->evm_->addEventSubscriber(new Doctrine\DBAL\Event\Listeners\MysqlSessionInit($conf['charset'], $conf['collation']));

            // @todo ask DBAL if the driver/db type supports nested transactions
            $pdo->setNestTransactionsWithSavepoints($this->isNestedTransactions());

            // @todo can we set these up earlier?
            $pdo->getDatabasePlatform()->registerDoctrineTypeMapping('blob', 'blob');
            $pdo->getDatabasePlatform()->registerDoctrineTypeMapping('mediumblob', 'mediumblob');
            // @todo enum: remove or add doctrine mapping type
            $pdo->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
            // alias boolean to boolean so ZMDbTableMapper maps continue to work
            $pdo->getDatabasePlatform()->registerDoctrineTypeMapping('boolean', 'boolean');

            $this->pdo_ = $pdo;
            $this->config_ = $conf;
        }
    }

    /**
     *  Initialize the entity manager
     *
     *  @todo where should it really go
     *  @todo probably could be shortened
     *  @todo rewrite it!
     *  @param mixed $conf
     */
    public function initEntityManager($conf = array()) {

        $config = new Doctrine\ORM\Configuration();

        $config->setProxyDir($conf['proxy_dir']);
        $config->setProxyNamespace($conf['proxy_namespace']);
        $config->setAutoGenerateProxyClasses($conf['auto_generate_proxy_classes']);

        $config->setQueryCacheImpl(new $conf['query_cache_driver']);
        $config->setResultCacheImpl(new $conf['result_cache_driver']);
        $config->setMetadataCacheImpl(new $conf['metadata_cache_driver']);

        $chainDriverImpl = new \Doctrine\ORM\Mapping\Driver\DriverChain();

        $mapping = $conf['mappings']['zenmagick'];
        foreach ((array)$mapping['dir'] as $dir) {
            $paths[] = Runtime::getInstallationPath().'/'.$dir;
        }
        $driverImpl = $config->newDefaultAnnotationDriver($paths);
        $chainDriverImpl->addDriver($driverImpl, $mapping['prefix']);
        $config->setMetadataDriverImpl($chainDriverImpl);

        $this->ormConfig_ = $config;

        // Table Prefix
        $tablePrefix = new zenmagick\base\database\doctrine\TablePrefix($this->config_['prefix']);
        // @todo it doesn't work on the DBAL, so while developing plugins it is recommended not to use a table prefix
        $this->evm_->addEventListener(Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);

        // @todo bring this back later
        //$timestampableListener = new Gedmo\Timestampable\TimestampableListener();
        //$this->evm_->addEventSubscriber($timestampableListener);
    }

    /**
     * Get an entity manager instance
     *
     * @param mixed $conf
     * @todo this is very niave, we can do better
     */
    public function getEntityManager($conf=null) {
        if (is_null($this->ormConfig_)) {
            $this->initEntityManager($conf);
        }
        if (is_null($this->em_)) {
            $this->em_ = Doctrine\ORM\EntityManager::create($this->pdo_, $this->ormConfig_, $this->evm_);
        }
        return $this->em_;
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
     * Get the configuration settings for this instance.
     *
     * @return array Configuration settings as set via the constructor.
     */
    public function getConfig() {
        return $this->config_;
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
            $this->ensureResource();
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
            $this->ensureResource();
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
            $this->ensureResource();
            $this->pdo_->rollBack();
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
    public function loadModel($table, $key, $modelClass, $mapping=null) {
        $mapping = $this->mapper_->ensureMapping(null !== $mapping ? $mapping : $table, $this);

        $keyName = Runtime::getSettings()->get('zenmagick.core.database.model.keyName');
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
    public function createModel($table, $model, $mapping=null) {
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
    public function removeModel($table, $model, $mapping=null) {
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
    public function updateModel($table, $model, $mapping=null) {
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
     * @param string sql The update sql.
     * @param mixed data A model instance or array; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @return array Details about the number of affected rows and last inserted id; example: array('rows' => 3, 'lastInsertId' => 3)
     * @throws ZMDatabaseException
     */
    public function update($sql, $data=array(), $mapping=null) {
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        // convert to array
        if (is_object($data)) {
            $data = Beans::obj2map($data, array_keys($mapping));
        }
        try {
            $stmt = $this->prepareStatement($sql, $data, $mapping);
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
     * @param array args Optional query args; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return mixed The (expected) single result or <code>null</code>
     * @throws ZMDatabaseException
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null) {
        $results = $this->query($sql, $args, $mapping, $modelClass);
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
     * @param array args Optional query args; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return array List of populated objects of class <code>$resultClass</code> or map if <em>modelClass</em> is <code>null</code>.
     * @throws ZMDatabaseException
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null) {
        $mapping = $this->mapper_->ensureMapping($mapping, $this);

        try {
            $stmt = $this->prepareStatement($sql, $args, $mapping);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        // make sure we are working on a map
        if (is_object($args)) {
            $args = Beans::obj2map($args, array_keys($mapping));
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

        // handle array args
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

        // defaults - i think we only want to use this on create
        /*foreach ($mapping as $field) {
            if (!array_key_exists($field['property'], $args) && null != $field['default']) {
                $args[$field['property']] = $field['default'];
            }
        }*/

        // create statement
        $this->ensureResource();
        $stmt = $this->pdo_->prepare($sql);
        foreach ($args as $name => $value) {
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
     * <p>Meta data is available for either the current database or, if specified, an individual table.</p>
     *
     * <p>The following table information will be returned:</p>
     * <dl>
     *  <dt>type</dt>
     *  <dd>The data type. This will be a data type as supported by the <code>ZMDatabase</code> API.</dd>
     *  <dt>name</dt>
     *  <dd>The (case sensitive) column name.</dd>
     *  <dt>key</dt>
     *  <dd>A boolean indicating a primary key</dd>
     *  <dt>autoIncrement</dt>
     *  <dd>A boolean flag indication an auto increment column</dd>
     *  <dt>maxLen</dt>
     *  <dd>The max. field length; this value is context specific.</dd>
     * </dl>
     *
     * @param string table Optional table; if no table is provided, database meta data will be returned;
     *  default is <code>null</code>.
     * @return array Context dependent meta data.
     * @throws ZMDatabaseException
     */
    public function getMetaData($table=null) {
        $this->ensureResource();
        $sm = $this->pdo_->getSchemaManager();
        if (null !== $table) {
            if (!empty($this->config_['prefix']) && 0 !== strpos($table, $this->config_['prefix'])) {
                $table = $this->config_['prefix'].$table;
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
                    'autoIncrement' => $column->getAutoincrement(),
					'maxLen' => $column->getLength(),/* TODO doesn't work for integers*/
		            'default' => $column->getDefault()
                 );
			}
			return $meta;
        } else {
            $tables = array();
            try {
                foreach ($sm->listTables() as $table) {
                    $tables[] = $table->getName();
                }
            } catch (Doctrine\DBAL\Schema\SchemaException $pdoe) {
                throw new ZMDatabaseException($pdoe->getMessage(), $pdoe->getCode(), $pdoe);
            }
            return array('tables' => $tables);
        }
    }

    /**
     * Get underlying PDO instance.

     * @return object
     */
    public function getResource() {
        $this->ensureResource();
        return $this->pdo_;
    }

}
