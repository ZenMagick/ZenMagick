<?php
/*
 * ZenMagick Core - Another PHP framework.
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
 * Caching service.
 *
 * <p>Commonly supported caching options are:</p>
 * <ul>
 *  <li><em>lifeTime</em> - the life time of cache entries in seconds</li>
 * </ul>
 *
 * <p>Right now the only implementation supported is <code>ZMFileCache</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.cache
 * @version $Id$
 */
class ZMCaches extends ZMObject {
    private static $DEFAULT_TYPES = array(ZMCache::PERSISTENT => 'file', ZMCache::TRANSIENT => 'memory');
    private $caches_;
    private $types_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->caches_ = array();
        $this->types_ = array_merge(self::$DEFAULT_TYPES, ZMSettings::get('zenmagick.core.cache.mapping.defaults', array()));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Caches');
    }


    /**
     * Get a cache instance for the given group and configuration.
     *
     * @param string group The cache group.
     * @param array config Configuration; default is an empty array.
     * @param string type Optional cache type; default is <code>ZMCache::PERSISTENT</code>.
     * @return ZMCache A cache instance or null.
     * @see ZMCache::PERSISTENT
     * @see ZMCache::TRANSIENT
     */
    public function getCache($group, $config=array(), $type=ZMCache::PERSISTENT) {
        ksort($config);
        $class = ucwords($this->types_[$type]).'Cache';
        $key = $group.':'.$class.':'.serialize($config);

        $instance = null;
        if (!isset($this->caches_[$key])) {
            $instance = ($instance = ZMLoader::make($class));
            $instance->init($group, $config);
            $this->caches_[$key] = array('instance' => $instance, 'group' => $group, 'config' => $config, 'type' => $type);
        } else {
            $instance = $this->caches_[$key]['instance'];
        }

        return $instance;
    }

    /**
     * Get a list of all active caches.
     *
     * @return array List of caches.
     */
    public function getCaches() {
        ksort($this->caches_);
        return $this->caches_;
    }

}

?>
