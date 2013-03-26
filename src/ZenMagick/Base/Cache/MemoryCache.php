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
namespace ZenMagick\Base\Cache;

use PEAR\Cache\CacheLite;

/**
 * Memory caching.
 *
 * <p>Memory caching using <code>PEAR:Cache_Lite</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MemoryCache implements Cache
{
    private $groups;
    private $group;
    private $cache;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        $groups = array();
    }

    /**
     * {@inheritDoc}
     */
    public function init($group, $config)
    {
        // set these, all others are passed through 'as is'
        $config['memoryCaching'] = true;
        $config['onlyMemoryCaching'] = true;
        $this->group = $group;
        $this->cache = new CacheLite($config);
        $this->groups[$group] = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->cache->clean($this->group);
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($id)
    {
        return $this->cache->get($id, $this->group);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        return $this->cache->remove($id, $this->group);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id)
    {
        return $this->cache->save($data, $id, $this->group);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified()
    {
        return $this->cache->lastModified();
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return array('lastModified' => time(), 'system' => $this->groups);
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($key, $value)
    {
    }

}
