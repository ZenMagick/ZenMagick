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
namespace zenmagick\http\templating\cache;

use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;
use zenmagick\base\ZMObject;
use zenmagick\http\templating\TemplateCache;

/**
 * Simple cache.
 *
 * <p>Template names to be cached are configured as list using the setting: <em>'zenmagick.http.template.cache.simple'</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SimpleTemplateCache extends ZMObject implements TemplateCache {
    private $cache_;


    /**
     * {@inheritDoc}
     */
    public function eligible($template) {
        Runtime::getLogging()->log('check if eligible: '.$template, LOGGING::TRACE);
        return in_array($template, (array)$this->container->get('settingsService')->get('zenmagick.http.template.cache.simple', array()));
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
     * @param zenmagick\base\cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache_ = $cache;
    }

    /**
     * Get the cache.
     *
     * @return zenmagick\base\cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache_;
    }

}
