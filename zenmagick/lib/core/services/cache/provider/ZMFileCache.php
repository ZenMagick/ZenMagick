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
 * File caching.
 *
 * <p>File caching using <code>PEAR:Cache_Lite</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.cache.provider
 */
class ZMFileCache extends ZMObject implements ZMCache {
    const SYSTEM_KEY = "org.zenmagick.core.services.cache.provider.file";
    private $group_;
    private $available_;
    private $cache_;
    private $metaCache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->available_ = true;
        $config['automaticSerialization'] = true;
        $config['cacheDir'] = ZMSettings::get('zenmagick.core.cache.provider.file.baseDir');
        $this->ensureCacheDir($config['cacheDir']);
        $this->metaCache_ = new Cache_Lite($config);
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
        // set these, all others are passed through 'as is'
        $config['automaticSerialization'] = true;
        $config['cacheDir'] = ZMSettings::get('zenmagick.core.cache.provider.file.baseDir').$group.DIRECTORY_SEPARATOR;
        $this->group_ = $group;
        $this->available_ = $this->ensureCacheDir($config['cacheDir']);
        $this->cache_ = new Cache_Lite($config);

    
        // update system stats
        $system = $this->metaCache_->get(self::SYSTEM_KEY);
        if (!$system) {
            $system = array();
            $system['groups'] = array();
        }
        $system['groups'][$group] = $config;
        $this->metaCache_->save($system, self::SYSTEM_KEY);
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() { return $this->available_; }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        return $this->cache_->clean($this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($id) {
        $this->cache_->clean($this->group_, 'old');
        return $this->cache_->get($id, $this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id) {
        return $this->cache_->remove($id, $this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id) {
        return $this->cache_->save($data, $id, $this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified() {
        return $this->cache_->lastModified();
    }


    /**
     * Ensure the given dir exists and is writeable.
     *
     * @param string dir The cache dir.
     * @return boolean <code>true</code> if the cache dir is usable, <code>false</code> if not.
     */
    private function ensureCacheDir($dir) {
        ZMFileUtils::mkdir($dir);
        return file_exists($dir) && is_writeable($dir);
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        return array('lastModified' => $this->metaCache_->lastModified(), 'configs' => $this->metaCache_->get(self::SYSTEM_KEY));
    }

}
