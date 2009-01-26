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


/** If used as modelClass parameter, the raw SQL data will be returned (no mapping, etc); deprecated */
define('ZM_DB_MODEL_RAW', '@raw');

/** Internal date format; deprecated */
define('ZM_DB_DATETIME_FORMAT', 'Y-m-d H:i:s');

/** NULL date; deprecated */
define('ZM_DB_NULL_DATE', '0001-01-01');
/** NULL datetime; deprecated */
define('ZM_DB_NULL_DATETIME', '0001-01-01 00:00:00');


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
 *  <dd>The database type; typical values would be: <em>mysql</em>, <em>sqlite</em> or <em>pgsql</em>.</dd>
 *  <dt>host</dt>
 *  <dd>The database host (and port).</dd>
 *  <dt>port</dt>
 *  <dd>The database port.</dd>
 *  <dt>username</dt>
 *  <dd>The database username.</dd>
 *  <dt>password</dt>
 *  <dd>The password for the database user.</dd>
 *  <dt>database</dt>
 *  <dd>The name of the database to connect to.</dd>
 * </dl>
 *
 * @author DerManoMann
 * @package org.zenmagick.database
 * @version $Id$
 */
interface ZMDatabase {
    /** If used as modelClass parameter, the raw SQL data will be returned (no mapping, etc). */
    const MODEL_RAW = '@raw';

    /** Internal date format. */
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
     * @return int The number of affected rows.
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
     * Enable/disable automatic commits for this instance.
     *
     * @param boolean value The new value.
     */
    public function setAutoCommit($value);

    /**
     * Commits statements in a transaction.
     */
    public function commit();

    /**
     * Rollback changes in a transaction.
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

?>
