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
use ZenMagick\Base\ZMObject;

use Symfony\Component\Filesystem\Filesystem;
/**
 * File caching.
 *
 * <p>File caching using <code>PEAR:Cache_Lite</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class FileCache extends ZMObject implements Cache
{
    const SYSTEM_KEY = "zenmagick.base.cache.file";
    private $group;
    private $available;
    private $cache;
    private $metaCache;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->available = true;
    }

    /**
     * {@inheritDoc}
     */
    public function init($group, $config)
    {
        if (!isset($config['cacheDir'])) {
            throw new \RuntimeException('missing cacheDir');
        }
        $defaults = array(
            'automaticSerialization' => true,
        );
        $config = array_merge($defaults, $config);
        if (array_key_exists('cacheTTL', $config)) {
            $config['lifeTime'] = $config['cacheTTL'];
            unset($config['cacheTTL']);
        }
        $this->ensureCacheDir($config['cacheDir']);
        $this->metaCache = new CacheLite($config);
        $this->group = $group;
        $this->available = $this->ensureCacheDir($config['cacheDir']);
        $this->cache = new CacheLite($config);

        // update system stats
        $system = $this->metaCache->get(self::SYSTEM_KEY);
        if (!$system) {
            $system = array();
            $system['groups'] = array();
        }
        $system['groups'][$group] = $config;
        $this->metaCache->save($system, self::SYSTEM_KEY);
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable() { return $this->available; }

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
        $this->cache->clean($this->group, 'old');

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
     * Ensure the given dir exists and is writeable.
     *
     * @param string dir The cache dir.
     * @return boolean <code>true</code> if the cache dir is usable, <code>false</code> if not.
     */
    private function ensureCacheDir($dir)
    {
        $filesystem = new Filesystem;
        $filesystem->mkdir($dir);

        return file_exists($dir) && is_writeable($dir);
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        return array('lastModified' => $this->metaCache->lastModified(), 'system' => $this->metaCache->get(self::SYSTEM_KEY));
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($key, $value)
    {
        $map = array('cacheTTL' => 'lifeTime');
        if (isset($map[$key])) {
            $key = $map[$key];
        }
        $this->cache->setOption($key, $value);
    }

}
