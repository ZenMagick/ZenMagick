<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2007 ZenMagick
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

    $zm_messages->_loadMessageStack();
    $zm_categories->setPath($cPath_array);

    // main request processor
    if (zm_setting('isEnableZenMagick')) {
        $zm_events->fireEvent($zm_runtime, ZM_EVENT_DISPATCH_START);
        if (zm_dispatch()) {
            $zm_events->fireEvent($zm_runtime, ZM_EVENT_DISPATCH_DONE);
            require('includes/application_bottom.php');
            $contents = ob_get_clean();
            $contents = $zm_plugins->filterResponse($contents);
            echo $contents;

            // page cache
            $_zm_pageCache = $zm_runtime->getPageCache();
            if (zm_setting('isPageCacheEnabled') && $_zm_pageCache->isCacheable()) {
                $_zm_pageCache->save($contents);
            }

            // clear messages if not redirect...
            $_zm_session = new ZMSession();
            $_zm_session->clearMessages();

            exit;
        }
    }

    // default to zen-cart

?>
