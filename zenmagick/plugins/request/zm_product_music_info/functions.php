<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @version $Id$
 */
?>
<?php


    /**
     * Media href.
     *
     * @package org.zenmagick.plugins.zm_product_music_info
     * @param string filename The media filename.
     * @param boolean echo If <code>true</code>, the formatted text will be echo'ed as well as returned.
     * @return A URL.
     * @deprecated use the new toolbox instead!
     */
    function zm_media_href($filename, $echo=ZM_ECHO_DEFAULT) {
        $href = DIR_WS_MEDIA.$filename;

        if ($echo) echo $href;
        return $href;
    }

?>
