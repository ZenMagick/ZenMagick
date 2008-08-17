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
 * Locale methods.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxLocale extends ZMObject {

    /**
     * Format and display a date(time) using the configured ui date format (<em>UI_DATE_FORMAT</em>).
     *
     * @param string date The date.
     * @param boolean echo If <code>true</code>, the date will be echo'ed as well as returned.
     * @return string The formatted date.
     */
    public function shortDate($date, $echo=ZM_ECHO_DEFAULT) { 
        $ds = ZMTools::translateDateString($date, ZM_DATETIME_FORMAT, UI_DATE_FORMAT); 
        if($echo) echo $ds;
        return $ds;
    }

}

?>
