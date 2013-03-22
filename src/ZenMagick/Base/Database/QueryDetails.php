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

use ZenMagick\Base\ZMObject;

/**
 * Query details as returned by the <code>ZenMagick\Base\Database\SqlAware</code> interface method.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class QueryDetails extends ZMObject
{
    private $database;
    private $sql;
    private $args;
    private $mapping;
    private $modelClass;
    private $countCol;

    /**
     * Create new instance.
     *
     * <p>The parameters here correspond to <code>ZenMagick\Base\Database\Connection#fetchAll()</code>.</p>
     *
     * @param ZenMagick\Base\Database\Connection database The database.
     * @param string sql The sql.
     * @param array args Database query parameter; default is <code>array()</code>.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @param string modelClass The class name; default is <code>null</code>.
     * @param string countCol The column SQL to use for counting; default is <code>null</code> to compute.
     */
    public function __construct($database, $sql, $args=array(), $mapping=null, $modelClass=null, $countCol=null)
    {
        parent::__construct();
        $this->database = $database;
        $this->sql = $sql;
        $this->args = $args;
        $this->mapping = $mapping;
        $this->modelClass = $modelClass;
        $this->countCol = $countCol;
    }

    /**
     * Get the database.
     *
     * @return string The database.
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get the sql.
     *
     * @return string The sql.
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the query parameter.
     *
     * @return array The parameter.
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Get the query mapping.
     *
     * @return mixed The mapping.
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Get the query model class.
     *
     * @return string The model class.
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Get the count column SQL.
     *
     * @return string The SQL fragment.
     */
    public function getCountCol()
    {
        return $this->countCol;
    }

    /**
     * Execute the query with either the original or alternative SQL.
     *
     * @param string sql Optional sql; default is <code>null</code> to use the original SQL.
     * @return mixed array Results.
     */
    public function query($sql=null)
    {
        return $this->database->fetchAll(null != $sql ? $sql : $this->sql, $this->args, $this->mapping, $this->modelClass);
    }

}
