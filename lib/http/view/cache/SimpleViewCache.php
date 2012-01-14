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
namespace zenmagick\http\view\cache;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\http\view\ViewCache;

/**
 * Simple caching.
 *
 * <p>Template names to be cached are configured as list using the setting: <em>'zenmagick.http.view.cache.simple'</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SimpleViewCache implements ViewCache {
    private $cache_;


    /**
     * {@inheritDoc}
     */
    public function eligible($template) {
        return in_array($template, $this->container->get('settingsService')->get('zenmagick.http.view.cache.simple'));
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
    public function store($template, $content) {
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
