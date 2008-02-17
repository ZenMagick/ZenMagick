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
 * $Id: functions.php 676 2008-02-01 02:14:28Z DerManoMann $
 */
?>
<?php

    /**
     * Group pricing admin page.
     *
     * @package org.zenmagick.plugins.zm_group_pricing
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_group_pricing_admin() {
    global $zm_group_pricing, $zm_nav_params;

        eval(zm_globals());
        $template = file_get_contents($zm_group_pricing->getPluginDir().'/views/group_pricing_admin.php');
        eval('?>'.$template);
        return new ZMPluginPage('zm_group_pricing_admin', zm_l10n_get('Group Pricing'));
    }

?>
