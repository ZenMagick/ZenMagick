<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

define('ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT', 'index,category,product_info,page,static,products_new,featured_products,specials,product_reviews');

    /**
     * Default caching strategy for page caching.
     *
     * <p>The strategy is as follows:</p>
     * <ol>
     *  <li>The request is not a <em>POST</em> request</li>
     *  <li>The shoppingcart is empty</li>
     *  <li>There are no messages that need to be  displayed</li>
     *  <li>The request's page name parameter is in the list of configured opt-in pages</li>
     * </ol>
     *
     * @package org.zenmagick.plugins.pageCache
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    function zm_page_cache_default_strategy($request) {
        return 'POST' != $request->getMethod()
          && (null == $request->getShoppingCart() || $request->getShoppingCart()->isEmpty())
          && !ZMMessages::instance()->hasMessages()
          && ZMLangUtils::inArray($request->getRequestId(), ZMSettings::get('plugins.pageCache.strategy.allowed', ZM_PLUGINS_PAGE_CACHE_ALLOWED_DEFAULT));
    }

?>
