<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Generic cache interface.
 *
 * <p>Instances may be obtained by using <code>ZMCaches::instance()->getCache(..)</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.cache
 * @version $Id: ZMCache.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
interface ZMCache {
    /** Cache type <em>persistent</em>. */
    const PERSISTENT = 'persistent';
    /** Cache type <em>transient</em>. */
    const TRANSIENT = 'transient';


    /**
     * Init the instance.
     *
     * @param string group The cache group/class.
     * @param array config Configuration.
     */
    public function init($group, $config);

    /**
     * Check if this cache instance is available.
     *
     * @return boolean <code>true</code> if this cache is ready to be used.
     */
    public function isAvailable();

    /**
     * Clear the cache.
     *
     * @return boolean <code>true</code> if cache cleared, <code>false</code> if the call failed.
     */
    public function clear();

    /**
     * Test if a valid cache entry exists and, if it does, return it
     *
     * @param string id The cache id.
     * @return string Cache data or <code>false</code>.
     */
    public function lookup($id);

    /**
     * Remove the cache entry for the given id.
     *
     * @param string id The cache id.
     */
    public function remove($id);

    /**
     * Save some data in a cache file
     *
     * @param mixed $data The data to be put in cache.
     * @param string id The cache id.
     * @return boolean <code>true</code> if saved, <code>false</code> if not.
     */
    public function save($data, $id);

    /**
     * Return the caches last modification time
     *
     * @return int last modification time
     */
    public function lastModified();

}

?>
