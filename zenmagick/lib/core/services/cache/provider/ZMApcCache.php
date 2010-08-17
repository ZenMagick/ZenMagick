<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * APC caching.
 *
 * <p>Persistent caching using <code>APC</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.cache.provider
 */
class ZMApcCache extends ZMObject implements ZMCache {
    const SYSTEM_KEY = "org.zenmagick.core.services.cache.provider.apc";
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

        // update system stats
        $system = apc_fetch(self::SYSTEM_KEY);
        if (!$system) {
            $system = array();
            $system['groups'] = array();
        }
        $system['groups'][$group] = $config;
        apc_store(self::SYSTEM_KEY, $system, 0);
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() {
        return function_exists('apc_cache_info');
    }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        $this->lastModified_ = time();

        $groupPrefix = $this->group_.'/';
        $cacheInfo = apc_cache_info('user');

        // iterate over all entries and match the group prefix
        foreach ($cacheInfo['cache_list'] as $entry) {
            if (0 === strpos($entry['info'], $groupPrefix)) {
                apc_delete($entry['info']);
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($id) {
		    return apc_fetch($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id) {
        $this->lastModified_ = time();
		    return apc_delete($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id) {
        $this->lastModified_ = time();
        return apc_store($this->group_.'/'.$id, $data, $this->lifetime_);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified() {
        return $this->lastModified_;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        return array('lastModified' => $this->lastModified(), 'system' => apc_fetch(self::SYSTEM_KEY));
    }

}
