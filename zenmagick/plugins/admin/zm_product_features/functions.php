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
 *
 * $Id$
 */
?>
<?php

    /**
     * Product features admin page.
     *
     * @package org.zenmagick.plugins.zm_product_features
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_product_features_admin() {
    global $zm_nav_params;

        $plugin = ZMPlugins::instance()->getPluginForId('zm_product_features');
        $template = file_get_contents($plugin->getPluginDirectory().'/views/manage_features.php');
        eval('?>'.$template);
        return new ZMPluginPage('zm_product_features_admin', zm_l10n_get('Features'));
    }

    /**
     * Create a list of values separated by the given separator string.
     *
     * @package org.zenmagick.plugins.zm_product_features
     * @param array list Array of values.
     * @param string sep Separator string; default: ', '.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A list of values.
     */
    function zm_list_values($list, $sep=', ', $echo=ZM_ECHO_DEFAULT) {
        $first = true;
        $html = '';
        foreach ($list as $value) {
            if (!$first) $html .= $sep;
            $first = false;
            $html .= $value;
        }

        if ($echo) echo $html;
        return $html;
    }

?>
