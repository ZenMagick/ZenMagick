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
 *
 * $Id$
 */
?>
<?php

    /**
     * Product associations admin page.
     *
     * @package org.zenmagick.plugins.zm_page_cache
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_page_cache_admin() {
    global $zm_request, $zm_messages, $zm_page_cache;

        if ('x' == $zm_request->getParameter('pageCache')) {
            $pageCache = ZMLoader::make('PageCache');
            $ok = $pageCache->clear();
            $zm_messages->add(zm_l10n_get('Clear page cache ' . ($ok ? 'successful' : 'failed')), $ok ? 'msg' : 'error');
        }

        $title = zm_l10n_get("Page Cache Manager");
        $action = zm_plugin_admin_url(false);
        $legend = zm_l10n_get("Clear Cache Options");
        $pageCacheLabel = zm_l10n_get("Clear Page Cache");
        $submitValue = zm_l10n_get("Clear");
        $contents = <<<EOT
<h2>$title</h2>
<form action="$action" method="POST">
  <fieldset class="cache">
  <legend>$legend</legend>
    <input type="checkbox" id="pageCache" name="pageCache" value="x">
    <label for="pageCache">$pageCacheLabel</label>
    <br>
    <div class="submit">
        <input type="submit" value="$submitValue">
    </div>
  </fieldset>
</form>
EOT;

        return new ZMPluginPage('zm_page_cache_admin', zm_l10n_get('Page Cache'), $contents);
    }

?>
