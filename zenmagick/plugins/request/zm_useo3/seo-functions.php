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
 */
?>
<?php


/**
 * ZenMagick SEO API function.
 */
function zm_build_seo_href($view=null, $params='', $isSecure=false) {
    if ($view == 'category') { $view = 'index'; }
    if (isset($GLOBALS['SeoUrl']) && (null == ZMSettings::get('seoEnabledPagesList') || ZMTools::inArray($view, ZMSettings::get('seoEnabledPagesList')))) {
        return $GLOBALS['SeoUrl']->buildHrefLink($view, $params, $isSecure ? 'SSL' : 'NONSSL');
    } else {
        return ZMToolbox::instance()->net->_zm_zen_href_link($view, $params, $isSecure ? 'SSL' : 'NONSSL');
    }
}

if (!function_exists(zen_href_link_stock)) {
    /**
     * This is the name of the renamed zen_href_link function in a vanilla USEO3 installation.
     */
    function zen_href_link_stock($page='', $params='', $connection='NONSSL', $add_session_id=true, $seo_safe=true, $static=false, $use_dir_ws_catalog=true) {
        return ZMToolbox::instance()->net->_zm_zen_href_link($page, $params, $connection, $add_session_id, $seo_safe, $static, $use_dir_ws_catalog);
    }
}

?>
