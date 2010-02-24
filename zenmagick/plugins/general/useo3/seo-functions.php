<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

if (!function_exists('zen_href_link_stock')) {
    /**
     * This is the name of the renamed zen_href_link function in a vanilla USEO3 installation.
     */
    function zen_href_link_stock($page='', $params='', $connection='NONSSL', $add_session_id=true, $seo_safe=true, $static=false, $use_dir_ws_catalog=true) {
        return ZMStoreDefaultSeoRewriter::furl($page, $params, $connection, $add_session_id, false, $static, $use_dir_ws_catalog);
    }
}

?>
