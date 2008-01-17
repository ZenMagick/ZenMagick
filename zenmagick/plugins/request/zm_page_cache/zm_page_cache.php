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
 * Plugin for page caching.
 *
 * <p><strong>Note:</strong> If a cache hit is found, this plugin will effectively terminate request handling at that point.
 * Therefore it is essential that this plugin is configured to be the last to run.</p>
 *
 * @package org.zenmagick.plugins.zm_page_cache
 * @author DerManoMann
 * @version $Id$
 */
class zm_page_cache extends ZMPlugin {
    var $pageCacheHandler_;

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('ZenMagick Page Cache', 'Simple file based page caching', '${plugin.version}');
        $this->setLoaderSupport('ALL');
        $this->setPreferredSortOrder(9999);

        // set default cache config
        $defaults = array(
            'pageCacheDir' => DIR_FS_SQL_CACHE."/zenmagick/pages/",
            'pageCacheTTL' => 300, // in sec.
            'pageCacheStrategyCallback' => 'zm_page_cache_request_cacheable'
        );
        foreach ($defaults as $key => $value) {
            if (null === zm_setting($key)) {
                zm_set_setting($key, $value);
            }
        }
    }

    /**
     * Default c'tor.
     */
    function zm_page_cache() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();
        $this->pageCacheHandler_ = $this->create('PageCacheHandler');
        $this->pageCacheHandler_->zcoSubscribe();

        $this->addMenuItem('zm_page_cache_admin', zm_l10n_get('Page Cache'), 'zm_page_cache_admin');
    }


    /**
     * Create the plugin handler.
     *
     * @return ZMPluginHandler A <code>ZMPluginHandler</code> instance or <code>null</code> if
     *  not supported.
     */
    function &createPluginHandler() {
        return $this->pageCacheHandler_;
    }

}

?>
