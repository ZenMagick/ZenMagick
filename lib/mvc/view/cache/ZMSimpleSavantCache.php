<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Simple caching.
 *
 * <p>Template names to be cached are configured as comma separated list using the setting: <em>'zenmagick.mvc.templates.simpleCache'</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.view.cache
 */
class ZMSimpleSavantCache implements ZMSavantCache {
    private $cache_;

    /**
     * {@inheritDoc}
     */
    public function lookup($tpl) {
        return $this->cache_->lookup($tpl);
    }

    /**
     * {@inheritDoc}
     */
    public function save($tpl, $contents) {
        if (ZMLangUtils::inArray($tpl, Runtime::getSettings()->get('zenmagick.mvc.templates.simpleCache.templates'))) {
            $this->cache_->save($contents, $tpl);
        }
    }

    /**
     * Set the cache.
     *
     * @param ZMCache cache The cache.
     */
    public function setCache($cache) {
        $this->cache_ = $cache;
    }

    /**
     * Get the cache.
     *
     * @return ZMCache The cache.
     */
    public function getCache() {
        return $this->cache_;
    }

}
