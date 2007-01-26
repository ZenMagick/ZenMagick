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
     * Checks, if the current page is a checkout page.
     * 
     * @package net.radebatz.zenmagick.html
     * @param bool includeCart If <code>true</code>, the shopping cart is considered a checkout page, too; (defaults to <code>true</code>)
     * @return <code>true</code> if the current page is a checkout page.
     */
    function zm_is_checkout_page($includeCart=true) {
    global $zm_request;

        $page = $zm_request->getPageName();
        return ($includeCard && 'shopping_cart' == $page) || !(false === strpos($page, 'checkout_'));
    }

?>
