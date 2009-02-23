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
 * Add support for querying the SQL parameters for a particular method on an object.
 *
 * <p>This would typically be implemented by service classes that want to support SQL based
 * result list handling.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.database
 * @version $Id$
 */
interface ZMSQLAware {

    /**
     * Get query details.
     *
     * @param string method The method name to query.
     * @param array args Parameter for the method; default is an empty array <code>array()</code>.
     * @return ZMQueryDetails Details about the query that would be used or <code>null</code for
     * unsupported methods.
     */
    public function getQueryDetails($method, $args=array());

}

?>
