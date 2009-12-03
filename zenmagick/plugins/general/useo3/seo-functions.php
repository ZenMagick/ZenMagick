<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
function zm_build_seo_href($view=null, $params='', $isSecure=false, $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
    if ($view == 'category') { $view = 'index'; }
    if (isset($GLOBALS['SeoUrl']) && (null == ZMSettings::get('seoEnabledPagesList') || ZMLangUtils::inArray($view, ZMSettings::get('seoEnabledPagesList')))) {
        // no $seo parameter
        return $GLOBALS['SeoUrl']->buildHrefLink($view, $params, $isSecure ? 'SSL' : 'NONSSL', $addSessionId, $isStatic, $useContext);
    } else {
        return ZMRequest::instance()->getToolbox()->net->furl($view, $params, $isSecure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext);
    }
}

if (!function_exists('zen_href_link_stock')) {
    /**
     * This is the name of the renamed zen_href_link function in a vanilla USEO3 installation.
     */
    function zen_href_link_stock($page='', $params='', $connection='NONSSL', $add_session_id=true, $seo_safe=true, $static=false, $use_dir_ws_catalog=true) {
        return ZMRequest::instance()->getToolbox()->net->furl($page, $params, $connection, $add_session_id, false, $static, $use_dir_ws_catalog);
    }
}

?>
