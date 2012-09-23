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
namespace ZenMagick\Http\Templating\Cache;

use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Templating\TemplateCache;

/**
 * Simple cache.
 *
 * <p>Template names to be cached are configured as list using the setting: <em>'zenmagick.http.templating.cache.simple'</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SimpleTemplateCache extends ZMObject implements TemplateCache {
    private $cache_;


    /**
     * {@inheritDoc}
     */
    public function eligible($template) {
//        $this->container->get('logger')->log('check if eligible: '.$template, LOGGING::TRACE);
        $settingsService = $this->container->get('settingsService');
        if (!$settingsService->exists('zenmagick.http.templating.cache.simple')) return;
        return in_array($template, (array)$settingsService->get('zenmagick.http.templating.cache.simple', array()));
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($template) {
        return $this->cache_->lookup($template);
    }

    /**
     * {@inheritDoc}
     */
    public function save($template, $content) {
        $this->cache_->save($content, $template);
    }

    /**
     * Set the cache.
     *
     * @param ZenMagick\Base\Cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache_ = $cache;
    }

    /**
     * Get the cache.
     *
     * @return ZenMagick\Base\Cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache_;
    }

}
