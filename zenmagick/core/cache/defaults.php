<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
     * </ol>
     *
     * @package net.radebatz.zenmagick.cache
     * @return bool <code>true</code> if the current request is cacheable, <code>false</code> if not.
     */
    function zm_is_page_cacheable() {
    global $zm_request, $zm_cart;
        
        return !$zm_request->isSecure() 
          && !zm_is_checkout_page(true) 
          && $zm_cart->isEmpty() 
          && false === strpos($zm_request->getPageName(), 'account');
    }

?>
