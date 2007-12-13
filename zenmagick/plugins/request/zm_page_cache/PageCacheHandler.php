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
 * Page cache handler.
 *
 * <p>This class implements all the actual page caching.</p>
 *
 * @package org.zenmagick.plugins.zm_page_cache
 * @author DerManoMann
 * @version $Id$
 */
class PageCacheHandler extends ZMPluginHandler {
    var $pageCache_;

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();
        $this->pageCache_ = $this->create('PageCache');
    }

    /**
     * Default c'tor.
     */
    function PageCacheHandler() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Theme resolved event handler.
     *
     * <p>This is the point during request handling where it is decided whether to
     * use the page cache or not.</p>
     *
     * @param array args Contains the final theme (key: 'theme').
     */
    function onZMThemeResolved($args) {
    global $zm_runtime, $zm_theme;

        // handle page caching
        if (zm_setting('isPageCacheEnabled')) {
            // need $zm_theme for PageCache::getId()
            $zm_theme = $args['theme'];
            if ($this->pageCache_->isCacheable() && $contents = $this->pageCache_->get()) {
                if (!zm_eval_if_modified_since($this->pageCache_->lastModified())) {
                    echo $contents;
                    if (zm_setting('isDisplayTimerStats')) {
                        $db = $zm_runtime->getDB();
                        echo '<!-- zm_page_cache stats: ' . round($db->queryTime(), 4) . ' sec. for ' . $db->queryCount() . ' queries; ';
                        echo 'page: ' . zm_get_elapsed_time() . ' sec.; ';
                        echo 'lastModified: ' . $this->pageCache_->lastModified() . ' -->';
                    }
                }
                require('includes/application_bottom.php');
                exit;
            }
        }
    }


    /**
     * Filter the response contents.
     *
     * @param string contents The contents.
     * @return string The modified contents.
     */
    function filterResponse($contents) {
        if (zm_setting('isPageCacheEnabled') && $this->pageCache_->isCacheable()) {
            $this->pageCache_->save($contents);
        }

        return $contents;
    }

}

?>
