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
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMCaches extends ZMObject {
    private $caches_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->caches_ = array();
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
        return parent::instance('Caches');
    }


    /**
     * Get a cache instance for the given group and configuration.
     *
     * @param string group The cache group.
     * @param array config Configuration; default is an empty array.
     * @param string type Optional cache type; default is <em>file</em>.
     * @return ZMCache A cache instance or null.
     */
    public function getCache($group, $config=array(), $type='file') {
        ksort($config);
        $class = ucwords($type).'Cache';
        $key = $class.':'.serialize($config);

        $instance = null;
        if (null == $this->caches_[$key]) {
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
