<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
     * Get the Google AdSense JavaScript for the box with the given id.
     *
     * <p>If the id is <code>null</code>, the id will be determined by analyzing the
     * current box filename.</p>
     *
     * @package net.radebatz.zenmagick.plugins
     * @param int id The box id; default is <code>null</code>.
     * @param bool echo If <code>true</code>, the code will be echo'ed as well as returned.
     * @return string The JavaScript or <code>null</code>.
     */
    function zm_google_adsense_get_js_for_box($id=null, $echo=true) {
    global $zm_google_adsense_boxes;

        if (null === $id) {
            $boxName = str_replace('.php', '', basename(__FILE__));
            $id = str_replace(_ZM_GOOGLE_ADSENSE_BOX_PREFIX, '', $boxName);
        }

        $js = $zm_google_adsense_boxes->get($id);

        if ($echo) echo $js;
        return $js;
    }

?>
