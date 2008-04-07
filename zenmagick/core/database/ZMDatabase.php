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
 * ZenMagick database abstractation.
 *
 * <p>A generic, lightweight database layer.</p>
 *
 * @author mano
 * @package org.zenmagick.database
 * @version $Id$
 */
interface ZMDatabase {

    /**
     * Execute a query.
     *
     * <p>If <code>$mapping</code> and <code>$resultClass</code> are <code>null</code>, the returned
     * list will contain a map of <em>columns</em> =&gt; <em>value</em> for each result.</p>
     *
     * @param string sql The query.
     * @param array args Optional query args; default is an empty array.
     * @param array mapping The field mapping; default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return array List of populated objects of class <code>$resultClass</code>.
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null);

    /**
     * Execute a query expecting a single result.
     *
     * @param string sql The query.
     * @param array args Optional query args; default is an empty array.
     * @param array mapping The field mapping; default is <code>null</code>.
     * @param string modelClass The class name to be used to build result obects; default is <code>null</code>.
     * @return mixed The (expected) single result or <code>null</code>
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null);

    /**
     * Update a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param array mapping The field mappings.
     */
    public function updateModel($table, $model, $mapping);

    /**
     * Update a single row using the provided SQL and model data.
     *
     * @param string sql The update sql.
     * @param mixed data A model instance or array.
     * @param array mapping The field mappings.
     */
    public function update($sql, $data, $mapping);

    /**
     * Create a single row using the given model and mapping.
     *
     * @param string table The table to update.
     * @param mixed model The model instance.
     * @param array mapping The field mappings.
     * @return mixed The model with the updated primary key.
     */
    public function createModel($table, $model, $mapping);

}

?>
