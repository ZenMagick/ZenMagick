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
        ZMSettings::append('zenmagick.core.cache.providers', 'apc,file,memcache,memory,xcache');
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
        return ZMRuntime::singleton('Caches');
    }


    /**
     * Get a cache instance for the given group and configuration.
     *
     * <p>If the type is specified and it is <strong>neither</strong> <code>ZMCache::PERSISTENT</code> nor
     * <code>ZMCache::TRANSIENT</code> it will be taken as actual implementation name.</p>
     *
     *
     * <p>The rule for generating the class name of the implementation class is * <code>ucwords[$type]Cache</code>.
     * So, for example a type value of <em>memcache</em> would result in <em>MemcacheCache</em>.</p>
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
        if (array_key_exists($type, $this->types_)) {
            $type = $this->types_[$type];
        }
        $class = ucwords($type).'Cache';
        $key = $group.':'.$class.':'.serialize($config);

        $instance = null;
        if (!isset($this->caches_[$key])) {
            $instance = ZMLoader::make($class);
            $instance->init($group, $config);
            $this->caches_[$key] = array('instance' => $instance, 'group' => $group, 'config' => $config, 'type' => $type);
        } else {
            $instance = $this->caches_[$key]['instance'];
        }

        return $instance;
    }

    /**
     * Get a list of all <strong>currently active</strong> caches.
     *
     * @return array List of caches.
     */
    public function getCaches() {
        ksort($this->caches_);
        return $this->caches_;
    }


    /**
     * Get a list of all providers.
     *
     * @return array List of all caches (instantiated).
     */
    public function getProviders() {
        $providers = array();
       foreach (explode(',', ZMSettings::get('zenmagick.core.cache.providers')) as $type) {
            $class = ucwords($type).'Cache';
            $obj = ZMBeanUtils::getBean($class);
            if (null != $obj && $obj->isAvailable()) {
                $providers[$type] = $obj;
            }
        }
        return $providers;
    }

}
