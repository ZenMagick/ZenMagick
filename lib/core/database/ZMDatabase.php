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


/**
 * ZenMagick database abstractation.
 *
 * <p>A generic, lightweight database layer.</p>
 *
 * <p>As a convention, implementation classes must support an array as single constructor argument.
 * This array (or map) will contain the connection details.</p>
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
interface ZMDatabase {
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


    /**
     * Get some stats about database usage.
     *
     * @return array A map with statistical data.
     */
    public function getStats();

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
    public function query($sql, $args=array(), $mapping=null, $modelClass=null);

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
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null);

    /**
     * Update using the provided SQL and data and model.
     *
     * @param string sql The update sql.
     * @param mixed data A model instance or array; default is an empty array.
     * @param mixed mapping The field mappings or table name (list); default is <code>null</code>.
     * @return array Details about the number of affected rows and last inserted id; example: array('rows' => 3, 'lastInsertId' => 3)
     * @throws ZMDatabaseException
     */
    public function update($sql, $data=array(), $mapping=null);

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
    public function loadModel($table, $key, $modelClass, $mapping=null);

    /**
     * Create a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @return mixed The model with the updated primary key.
     * @throws ZMDatabaseException
     */
    public function createModel($table, $model, $mapping=null);

    /**
     * Update a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @throws ZMDatabaseException
     */
    public function updateModel($table, $model, $mapping=null);

    /**
     * Remove a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @throws ZMDatabaseException
     */
    public function removeModel($table, $model, $mapping=null);

    /**
     * Start a transaction.
     *
     * <p>If the database provider (and database driver) allow, nested transaction are possible.</p>
     *
     * @throws ZMDatabaseException
     */
    public function beginTransaction();

    /**
     * Commits statements in a transaction.
     *
     * @throws ZMDatabaseException
     */
    public function commit();

    /**
     * Rollback changes in a transaction.
     *
     * @throws ZMDatabaseException
     */
    public function rollback();

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
    public function getMetaData($table=null);

    /**
     * Get the configuration settings for this instance.
     *
     * @return array Configuration settings as set via the constructor.
     */
    public function getConfig();

    /**
     * Get the underlying, implementation specific resource used to access the database.
     *
     * @return mixed The (native) database handle, etc.
     */
    public function getResource();

}
