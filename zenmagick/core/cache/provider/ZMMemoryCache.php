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
 * Memocy caching.
 *
 * <p>Memocy caching using <code>PEAR:Cache_Lite</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.cache.provider
 * @version $Id: ZMFileCache.php 1061 2008-04-21 23:31:35Z DerManoMann $
 */
class ZMMemoryCache extends ZMObject implements ZMCache {
    private $group_;
    private $cache_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        $config['memoryCaching'] = true;
        $config['onlyMemoryCaching'] = true;
        $this->group_ = $group;
        $this->cache_ = new Cache_Lite($config);
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() { return true; }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        return $this->cache_->clean($this->group_);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id) {
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
    
}

?>
