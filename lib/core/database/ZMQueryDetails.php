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

use zenmagick\base\ZMObject;

/**
 * Query details as returned by the <code>ZMSQLAware</code> interface method.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.database
 */
class ZMQueryDetails extends ZMObject {
    private $database_;
    private $sql_;
    private $args_;
    private $mapping_;
    private $modelClass_;
    private $countCol_;


    /**
     * Create new instance.
     *
     * <p>The parameters here correspond to <code>ZMDatabase#fetchAll()</code>.</p>
     *
     * @param ZMDatabase database The database.
     * @param string sql The sql.
     * @param array args Database query parameter; default is <code>array()</code>.
     * @param mixed mapping The field mappings; default is <code>null</code>.
     * @param string modelClass The class name; default is <code>null</code>.
     * @param string countCol The column SQL to use for counting; default is <code>null</code> to compute.
     */
    public function __construct($database, $sql, $args=array(), $mapping=null, $modelClass=null, $countCol=null) {
        parent::__construct();
        $this->database_ = $database;
        $this->sql_ = $sql;
        $this->args_ = $args;
        $this->mapping_ = $mapping;
        $this->modelClass_ = $modelClass;
        $this->countCol_ = $countCol;
    }


    /**
     * Get the database.
     *
     * @return string The database.
     */
    public function getDatabase() {
        return $this->database_;
    }

    /**
     * Get the sql.
     *
     * @return string The sql.
     */
    public function getSql() {
        return $this->sql_;
    }

    /**
     * Get the query parameter.
     *
     * @return array The parameter.
     */
    public function getArgs() {
        return $this->args_;
    }

    /**
     * Get the query mapping.
     *
     * @return mixed The mapping.
     */
    public function getMapping() {
        return $this->mapping_;
    }

    /**
     * Get the query model class.
     *
     * @return string The model class.
     */
    public function getModelClass() {
        return $this->modelClass_;
    }

    /**
     * Get the count column SQL.
     *
     * @return string The SQL fragment.
     */
    public function getCountCol() {
        return $this->countCol_;
    }

    /**
     * Execute the query with either the original or alternative SQL.
     *
     * @param string sql Optional sql; default is <code>null</code> to use the original SQL.
     * @return mixed array Results.
     */
    public function query($sql=null) {
        return $this->database_->fetchAll(null != $sql ? $sql : $this->sql_, $this->args_, $this->mapping_, $this->modelClass_);
    }

}
