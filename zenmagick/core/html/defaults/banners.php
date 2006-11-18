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
     * Display the given banner.
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param ZMBanner banner A <code>ZMBanner</code> instance.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @param bool updateCount If <code>true</code>, the banner counter will get incremented.
     * @return string The HTML formatted banner.
     */
    function zm_display_banner($banner, $echo=true, $updateCount=true) {
        $html = '';

        if (null == $banner)
            return $html;

        if (!zm_empty($banner->getText())) {
            // use text if not empty
            $html = $banner->getText();
        } else {
            $img = '<img src="'.zm_image_href($banner->getImage(), false).'" alt="'.$banner->getTitle().'" />';
            if (zm_empty($banner->getUrl())) {
                // if we do not have a url try our luck with the image...
                $html = $img;
            } else {
                $target = $banner->isNewWin() ? (zm_setting('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"') : '';
                $html = '<a href="'.zm_redirect_href('banner', $banner->getId(), false).'"'.$target.'>'.$img.'</a>';
            }
        }

        if ($updateCount) {
            zen_update_banner_display_count($banner->getId());
        }

        if ($echo) echo $html;
        return $html;
    }
 

?>
