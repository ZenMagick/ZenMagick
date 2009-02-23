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
 * Query details as returned by the <code>ZMSQLAware</code> interface method.
 *
 * @author DerManoMann
 * @package org.zenmagick.database
 * @version $Id$
 */
class ZMQueryDetails extends ZMObject {
    private $sql_;
    private $args_;
    private $model_;
    private $modelClass_;


    /**
     * Create new instance.
     *
     * <p>The parameters here correspond to <code>ZMDatabase#query()</code>.</p>
     *
     * @param string sql The sql.
     * @param array args Database query parameter.
     * @param mixed mapping The field mappings.
     * @param string modelClass The class name.
     */
    public function __construct($sql, $args, $mapping, $modelClass) {
        parent::__construct();
        $this->sql_ = $sql;
        $this->args_ = $args;
        $this->model_ = $model;
        $this->modelClass_ = $modelClass;
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
     * Execute the query with either the original or alternative SQL.
     *
     * @param string sql Optional sql; default is <code>null</code> to use the original SQL.
     * @return mixed array Results.
     */
    public function query($sql=null) {
        return ZMRuntime::getDatabase()->query(null != $sql ? $sql : $this->sql_, $this->args_, $this->mapping_, $this->modelClass_);
    }

}

?>
