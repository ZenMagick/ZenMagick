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

use Memcache;

/**
 * Memcache caching.
 *
 * <p>Persistent caching using <code>memcache</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MemcacheCache implements Cache
{
    const SYSTEM_KEY = "zenmagick.base.cache.memcache";
    private $group;
    private $memcache;
    private $lifetime;
    private $lastModified;
    private $compress;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        $this->memcache = null;
        $this->lifetime = 0;
        $this->lastModified = time();
        $this->compress = 0;
    }

    /**
     * Get a ready-to-use <code>Memcache</code> instance.
     *
     * @param array config Optional config values: default is an empty array.
     * @return Memcache A <code>Memcache</code> instance.
     */
    protected function getMemcache($config=array())
    {
        if (null == $this->memcache) {
            $this->memcache = new Memcache();
            $config = array_merge(array('host' => 'localhost', 'port' => 11211), $config);
            $this->memcache->connect($config['host'], $config['port']);
        }

        return $this->memcache;
    }

    /**
     * {@inheritDoc}
     */
    public function init($group, $config)
    {
        $this->group = $group;
        $this->memcache = $this->getMemcache($config);
        $config = array_merge(array('cacheTTL' => 0, 'compress' => false), $config);
        $this->lifetime = $config['cacheTTL'];
        $this->compress = $config['compress'] ? MEMCACHE_COMPRESSED : 0;

        // update system stats
        $system = $this->memcache->get(self::SYSTEM_KEY);
        if (!$system) {
            $system = array();
            $system['groups'] = array();
        }
        $system['groups'][$group] = $config;
        $this->memcache->set(self::SYSTEM_KEY, $system, false, 0);
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable()
    {
        return class_exists('Memcache');
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->lastModified = time();

        // iterate over all entries and match the group prefix
        $groupPrefix = $this->group.'/';
        foreach ($this->memcache->getExtendedStats('items') as $host => $hostSummary) {
            foreach ($hostSummary['items'] as $slabId => $details) {
                $slabItems = $this->memcache->getExtendedStats('cachedump', $slabId, $details['number']);
                $keys = array_keys($slabItems[$host]);
                foreach ($keys as $key) {
                    if (0 === strpos($key, $groupPrefix)) {
                        $this->memcache->delete($key);
                    }
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($id)
    {
        return $this->memcache->get($this->group.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id)
    {
        $this->lastModified = time();

        return $this->memcache->delete($this->group.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id)
    {
        $this->lastModified = time();

        return $this->memcache->set($this->group.'/'.$id, $data, $this->compress, $this->lifetime);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return array('lastModified' => $this->lastModified(), 'system' => $this->getMemcache()->get(self::SYSTEM_KEY));
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($key, $value)
    {
    }

}
