<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Theme view that supports caching.
 *
 * <p>Uses PEAR:Cache_Lite as underlying cache mechanism.</p>
 *
 * <p>The <em>private</em> method <code>_isCacheable()</code> controls whether to
 * cache a request or not. Currently, all guest requests are deemed good for caching.</p>
 *
 * <p>Caching can be controlled using the following ZenMagick settings:</p>
 * <dl>
 *  <dt>isPageCacheEnabled</dt>
 *  <dd>Boolean to enable/disable caching</dd>
 *  <dt>pageCacheDir</dt>
 *  <dd>The directory for cache files.</dd>
 *  <dt>pageCacheTTL</dt>
 *  <dd>Time to live for cache files in seconds</dd>
 * </dl>
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.views.cache
 * @version $Id$
 */
class ZMCachedThemeView extends ZMThemeView {

    /**
     * Create new view.
     *
     * @param string page The page (view) name.
     */
    function ZMCachedThemeView($page) {
        parent::__construct($page);
    }

    /**
     * Create new view.
     *
     * @param string page The page (view) name.
     */
    function __construct($page) {
        $this->ZMCachedThemeView($page);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Generate view response.
     */
    function generate() { 
    global $zm_runtime;

        $pageCache = $zm_runtime->getPageCache();
        if (!$pageCache->isCacheable()) {
            parent::generate();
            return;
        } else {
            // not in cache
            ob_start();
            parent::generate();
            $contents = ob_get_flush();
            $pageCache->save($contents);
        }

    }

}

?>
