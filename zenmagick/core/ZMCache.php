<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Cache base class.
 *
 * <p>Implements basic caching. delegating the actual cache operations to the
 * underlying <code>PEAR:Cache_Lite</code>.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMCache extends ZMObject {
    var $group_;
    var $config_;
    var $cache_;
    var $available_;


    /**
     * Default c'tor.
     *
     * @param string group The cache group.
     * @param array config Configuration.
     */
    function ZMCache($group, $config) {
        parent::__construct();

        $this->group_ = $group;
        $this->_ensureCacheDir($config['cacheDir']);
        $this->cache_ = new Cache_Lite($config);
    }

    /**
     * Default c'tor.
     *
     * @param string group The cache group.
     * @param array config Configuration.
     */
    function __construct($group, $config) {
        $this->ZMCache($group, $config);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Evalute if this cache is available.
     *
     * @return bool <code>true</code> if this cache is ready to be used.
     */
    function isAvailable() { return $this->available_; }

    /**
     * Get the group.
     *
     * @return string The group.
     */
    function getGroup() {
        return $this->group_;
    }

    /**
     * Clear the cache.
     *
     * @return bool <code>true</code> if cache cleared, <code>false</code> if the call failed.
     */
    function clear() {
        return $this->cache_->clean($this->group_);
    }

    /**
     * Evaluates whether the implemented resource is cacheable in the context
     * of the current request.
     *
     * @return string A cache id or <code>null</code>.
     */
    function isCacheable() {
        return null;
    }

    /**
     * Create unique id for the context of the current request.
     *
     * @return string A cache id or <code>null</code>.
     */
    function getId() {
        return null;
    }

    /**
     * Test if a cache is available and (if yes) return it
     *
     * @param string id Optional cache id; if not set, the result of <code>getId()</code> will be used.
     * @return string Data of the cache (else : false)
     */
    function get($id=null) {
        return $this->cache_->get(null !== $id ? $id : $this->getId(), $this->group_);
    }

    /**
     * Save some data in a cache file
     *
     * @param string $data data to put in cache (can be another type than strings if automaticSerialization is on)
     * @param string id Optional cache id; if not set, the result of <code>getId()</code> will be used.
     * @return boolean true if no problem (else : false or a PEAR_Error object)
     */
    function save($data, $id=null) {
        return $this->cache_->save($data, null !== $id ? $id : $this->getId(), $this->group_);
    }


    /**
     * Ensure the given dir exists and is writeable.
     *
     * @param string dir The cache dir.
     */
    function _ensureCacheDir($dir) {
        zm_mkdir($dir, 755);

        $this->available_ = file_exists($dir) && is_writeable($dir);
    }

}

?>
