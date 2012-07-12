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
namespace zenmagick\base\database;

/**
 * Add support for querying the SQL parameters for a particular method on an object.
 *
 * <p>This would typically be implemented by service classes that want to support SQL based
 * result list handling.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
interface SqlAware {

    /**
     * Get query details.
     *
     * @param string method The method name to query; default is <code>null</code> for none.
     * @param array args Parameter for the method; default is an empty array <code>array()</code>.
     * @return zenmagick\base\database\QueryDetails Details about the query that would be used or <code>null</code for
     * unsupported methods.
     */
    public function getQueryDetails($method=null, $args=array());

}
