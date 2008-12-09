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
 * xcache caching.
 *
 * <p>Persistent caching using <code>xcache</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.cache.provider
 * @version $Id$
 */
class ZMXcacheCache extends ZMObject implements ZMCache {
    private static $GROUP_KEY = 'org.zenmagick.cache.provider.ZMXcacheCache';
    private $group_;
    private $lifetime_;
    private $lastModified_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->lifetime_ = 0;
        $this->lastModified_ = time();
        if (false) {
            for ($ii = 0, $max = xcache_count(XC_TYPE_VAR); $ii < $max; $ii++) {
                xcache_clear_cache(XC_TYPE_VAR, $ii);
            }
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function init($group, $config) {
        $this->group_ = $group;
        $this->lifetime_ = $config['cacheTTL'];
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() { 
        return function_exists('xcache_info');
    }

    /**
     * Add the given id to this instance's group.
     *
     * @param string id The id.
     */
    protected function addToGroup($id) {
        $groupCache = xcache_get(self::$GROUP_KEY);
        if (!isset($groupCache)) {
            $groupCache = array();
        }
        if (!isset($groupCache[$this->group_])) {
            $groupCache[$this->group_] = array();
        }
        $groupCache[$this->group_][$id] = $id;
        xcache_set(self::$GROUP_KEY, $groupCache, 0);
    }

    /**
     * Remove the given id from this instance's group.
     *
     * @param string id The id; default is <code>null</code> to remove all.
     */
    protected function removeFromGroup($id=null) {
        $groupCache = xcache_get(self::$GROUP_KEY);
        if (!isset($groupCache)) {
            return;
        }
        if (!isset($groupCache[$this->group_])) {
            return;
        }
        if (null === $id) {
            $groupCache[$this->group_] = array();
        } else {
            unset($groupCache[$this->group_][$id]);
        }
        xcache_set(self::$GROUP_KEY, $groupCache, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        $groupCache = xcache_get(self::$GROUP_KEY);
        if (!isset($groupCache)) {
            return;
        }
        if (!isset($groupCache[$this->group_])) {
            return;
        }
        foreach ($groupCache[$this->group_] as $id) {
            xcache_unset($this->group_.'/'.$id);
        }
        $this->removeFromGroup();
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($id) {
        if (!xcache_isset($this->group_.'/'.$id)) {
            return false;
        }
        return xcache_get($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id) {
        $this->removeFromGroup($id);
        return xcache_unset($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id) {
        $this->addToGroup($id);
        $this->lastModified_ = time();
        return xcache_set($this->group_.'/'.$id, $data, $this->lifetime_);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified() {
        return $this->lastModified_;
    }

}

?>
