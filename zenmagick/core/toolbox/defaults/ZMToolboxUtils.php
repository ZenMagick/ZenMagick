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
 * Generic utilities.
 *
 * @author mano
 * @package org.zenmagick.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxUtils extends ZMObject {

    /**
     * Simple title generator based on the page name.
     *
     * @param string page The page name; default is <code>null</code> for the current page.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A reasonable page title.
     */
    public function getTitle($page=null, $echo=ZM_ECHO_DEFAULT) {
        $title = null == $page ? ZMRequest::getPageName() : $page;
        // special case for static pages
        $title = 'static' != $title ? $title : ZMRequest::getSubPageName();

        // format
        $title = str_replace('_', ' ', $title);
        // capitalise words
        $title = ucwords($title);
        $title = zm_l10n_get($title);

        if ($echo) echo $title;
        return $title;
    }

}

?>
