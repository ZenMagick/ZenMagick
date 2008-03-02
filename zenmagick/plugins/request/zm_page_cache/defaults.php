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
     * Default caching strategy for page caching.
     *
     * <p>The strategy is as follows:</p>
     * <ol>
     *  <li>The request is not a secure request</li>
     *  <li>The page is not a checkout page</li>
     *  <li>The shoppingcart is empty</li>
     *  <li>There are no messages that need to be  displayed</li>
     * </ol>
     *
     * @package org.zenmagick.plugins.zm_page_cache
     * @return boolean <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    function zm_page_cache_request_cacheable() {
    global $zm_request, $zm_cart;
        
        return !$zm_request->isSecure() 
          && !zm_is_checkout_page(true) 
          && $zm_cart->isEmpty() 
          && !ZMMessages::instance()->hasMessages()
          && false === strpos($zm_request->getPageName(), 'ajax')
          && false === strpos($zm_request->getPageName(), 'address_book')
          && false === strpos($zm_request->getPageName(), 'account');
    }

?>
