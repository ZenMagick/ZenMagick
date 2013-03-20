<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\Base\Database;

use PDO;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMException;
use ZenMagick\Base\Database\TableMapper;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
 * ZenMagick database abstractation based on Doctrine DBAL
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
 */
class Connection extends DbalConnection
{
    /** If used as modelClass parameter, the raw SQL data will be returned (no mapping, etc). */
    const MODEL_RAW = '@raw';

    /** NULL date. */
    const NULL_DATE = '0001-01-01';

    /** NULL datetime. */
    const NULL_DATETIME = '0001-01-01 00:00:00';

    protected $mapper_;

   /**
     * Gets the table prefix used by this connection.
     *
     * @return string
     */
    public function getPrefix()
    {
        $params = $this->getParams();

        return isset($params['driverOptions']['table_prefix']) ? $params['driverOptions']['table_prefix'] : null;
    }

    /**
     * Figure out used table name.
     *
     * @param string table
     * @return table (possibly prefixed)
     */
    public function resolveTable($table)
    {
        $prefix = $this->getPrefix();
        if (null != $prefix && 0 !== strpos($table, $prefix)) {
            $table = $prefix.$table;
            }

        return $table;
    }

    /**
     * Get a table mapper insance.
     *
     * @return object
     */
    public function getMapper()
    {
        if (null == $this->mapper_) {
            $this->mapper_ = new TableMapper();
            $this->mapper_->setTablePrefix($this->getPrefix());
            $this->getDatabasePlatform()->registerDoctrineTypeMapping('boolean', 'boolean');
        }

        return $this->mapper_;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($tableName, array $identifier = array())
    {
        return parent::delete($this->resolveTable($tableName), $identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function insert($tableName, array $data, array $types = array())
    {
        return parent::insert($this->resolveTable($tableName), $data, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function update($tableName, array $data, array $identifier = array(), array $types = array())
    {
        return parent::update($this->resolveTable($tableName), $data, $identifier, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        return parent::executeUpdate($this->resolveTablePlaceHolders($query), $params, $types);
    }

    /**
     * {@inheritDoc}
     */
    public function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = null)
    {
        return parent::executeQuery($this->resolveTablePlaceHolders($query), $params, $types, $qcp);
    }

    /**
     * Resolve table names in SQL queries
     *
     * In the future it might be better to use in the prepare() method. It would need
     * more testing though.
     *
     * @param string SQL query
     * @return string sql query with %table.table_name% replaced with $prefix.table_name
     */
    public function resolveTablePlaceHolders($sql)
    {
        $prefix = $this->getPrefix();

        return preg_replace_callback('/%table\.(\w+?)%/', function($matches) use ($prefix) {
            return $prefix.$matches[1];
        }, $sql);
    }

    /**
     * Load a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed key The primary key.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @return mixed The model with the updated primary key.
     */
    public function loadModel($table, $key, $modelClass, $mapping = null)
    {
        $table = $this->resolveTable($table);
        $mapping = $this->getMapper()->ensureMapping(null !== $mapping ? $mapping : $table);

        // determine by looking at key and auto settings
        foreach ($mapping as $property => $field) {
            if ($field['auto'] && $field['key']) {
                $keyName = $property;
                break;
            }
        }

        $field = $mapping[$keyName];
        $sql = 'SELECT * from '.$table.' WHERE '.$field['column'].' = :'.$keyName;

        // @todo ensureMapping doesn't like being passed a completed mapping instance. so pass $table as $mapping
        return $this->querySingle($sql, array($keyName => $key), $table, $modelClass);
    }

    /**
     * Create a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @return mixed The model with the updated primary key.
     */
    public function createModel($table, $model, $mapping = null)
    {
        if (null === $model) return null;
        $table = $this->resolveTable($table);
        $mapping = $this->getMapper()->ensureMapping(null !== $mapping ? $mapping : $table);
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

        $stmt = $this->prepareStatement($sql, $modelData, $mapping);
        $stmt->execute();
        $newId = $this->lastInsertId();
        $stmt->closeCursor();

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
     */
    public function removeModel($table, $model, $mapping = null)
    {
        if (null === $model) return null;
        $table = $this->resolveTable($table);
        $mapping = $this->getMapper()->ensureMapping(null !== $mapping ? $mapping : $table);

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
            throw new ZMException('missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $modelData, $mapping);
        $stmt->execute();
        $stmt->closeCursor();
    }

    /**
     * Update a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     */
    public function updateModel($table, $model, $mapping = null)
    {
        if (null === $model) return null;
        $table = $this->resolveTable($table);
        $mapping = $this->getMapper()->ensureMapping(null !== $mapping ? $mapping : $table);

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
            throw new ZMException('missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $stmt->execute();
        $stmt->closeCursor();
    }

    /**
     * Update using the provided SQL and data and model.
     *
     * @param string query The update sql query.
     * @param mixed data A model instance or array; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @return int affected rows
     */
    public function updateObj($query, $params = array(), $mapping = null)
    {
        $mapping = $this->getMapper()->ensureMapping($mapping);

        // convert to array
        if (is_object($params)) {
            $params = Beans::obj2map($params, array_keys($mapping));
        }
        $stmt = $this->prepareStatement($query, $params, $mapping);
        $stmt->execute();
        $rows = $stmt->rowCount();
        $stmt->closeCursor();

        return $rows;
    }

    /**
     * Execute a query expecting a single result.
     *
     * <p><code>$modelClass</code> may be set to the magic value of <code>Connection::MODEL_RAW</code> to force
     * returning the raw data without applying any mappings or conversions.</p>
     *
     * @param string sql The query.
     * @param array params Optional query parameters; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return mixed The (expected) single result or <code>null</code>
     */
    public function querySingle($sql, array $params = array(), $mapping = null, $modelClass = null)
    {
        $results = $this->fetchAll($sql, $params, $mapping, $modelClass);

        return 0 < count($results) ? $results[0] : null;
    }

    /**
     * Execute a query.
     *
     * <p>If <code>$resultClass</code> is <code>null</code>, the returned
     * list will contain a map of <em>columns</em> =&gt; <em>value</em> for each selected row.</p>
     *
     * <p><code>$modelClass</code> may be set to the magic value of <code>Connection::MODEL_RAW</code> to force
     * returning the raw data without applying any mappings or conversions.</p>
     *
     * @param string sql The query.
     * @param array params Optional query parameters; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return array List of populated objects of class <code>$resultClass</code> or map if <em>modelClass</em> is <code>null</code>.
     */
    public function fetchAll($sql, array $params = array(), $mapping = null, $modelClass = null)
    {
        $mapping = $this->getMapper()->ensureMapping($mapping);

        $stmt = $this->prepareStatement($sql, $params, $mapping);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (self::MODEL_RAW == $modelClass) return $rows;

        $results = array();
        foreach ($rows as $result) {
            if (null !== $mapping) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass) {
                if (null != ($obj = new $modelClass)) {
                    if ($obj instanceof \Symfony\Component\DependencyInjection\ContainerAwareInterface) {
                        $obj->setContainer(Runtime::getContainer());
                    }
                }
                $result = Beans::setAll($obj, $result);
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
    protected function prepareStatement($sql, $params, $mapping = null)
    {
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
        $sql = $this->resolveTablePlaceHolders($sql);
        $stmt = $this->prepare($sql);
        foreach ($params as $name => $value) {
            $typeName = preg_replace('/[0-9]+'.$PDO_INDEX_SEP.'/', '', $name);
            if (false !== strpos($sql, ':'.$name) && array_key_exists($typeName, $mapping)) {
                // only bind if actually used
                $type = $mapping[$typeName]['type'];

                // @todo do we really want to keep self::NULL_DATE* for native ZM code/plugins or keep it at all?
                if ('datetime' == $type && null == $value) {
                    $value = self::NULL_DATETIME;
                }
                if ('date' == $type && null == $value) {
                   $value = self::NULL_DATE;
                }

                $dbalType = $this->getDatabasePlatform()->getDoctrineTypeMapping($type);
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
    protected function translateRow($row, $mapping)
    {
        if (null == $mapping) {
            return $row;
        }

        $mappedRow = array();
        $mappedFields = array();
        foreach ($mapping as $field) {
            if (array_key_exists($field['column'], $row)) {
                if ($field['type'] == 'date' && $row[$field['column']] == self::NULL_DATE) {
                    $row[$field['column']] = null;
                }
                if ($field['type'] == 'datetime' && $row[$field['column']] == self::NULL_DATETIME) {
                    $row[$field['column']] = null;
                }

                $mappedRow[$field['property']] = $this->convertToPHPValue($row[$field['column']], $field['type']);
                $mappedFields[$field['column']] = $field['column'];
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
     *  <dd>The data type. This will be a data type as supported by the <code>Doctrine\DBAL\Types</code> API.</dd>
     *  <dt>name</dt>
     *  <dd>The (case sensitive) column name.</dd>
     *  <dt>key</dt>
     *  <dd>A boolean indicating a primary key</dd>
     *  <dt>auto</dt>
     *  <dd>A boolean flag indication an auto increment column</dd>
     *  <dt>length</dt>
     *  <dd>The max. field length; this value is context specific.</dd>
     * </dl>
     *
     * @param string table table to get metadata from
     * @return array Context dependent meta data.
     */
    public function getMetaData($table)
    {
        $table = $this->resolveTable($table);
        $sm = $this->getSchemaManager();

        $meta = array();

        $tableDetails = $sm->listTableDetails($table);

        // TODO: yes we have a table without a primary key :(
        $primaryKey = $tableDetails->getPrimaryKey();
        $keys = is_object($primaryKey) ? $primaryKey->getColumns() : array();

            foreach ($tableDetails->getColumns() as $column) {
                $meta[$column->getName()] = array(
                'column' => $column->getName(),
                'type' => $column->getType()->getName(),
                'key' => in_array($column->getName(), $keys),
                'auto' => $column->getAutoincrement(),
                'length' => $column->getLength(),/* TODO doesn't work for integers*/
                'default' => $column->getDefault()
             );
        }

        return $meta;
    }
}
