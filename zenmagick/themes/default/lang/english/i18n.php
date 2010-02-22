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
 * $Id$
 */
?>
<?php

    zm_i18n_add(array(
        // system locale
        'LC_TIME' => 'en_US.ISO_8859-1',

        // to format dates
        'DATE_FORMAT' => 'd/m/Y',

        // to parse user input 
        'UI_DATE_FORMAT' => 'dd/mm/yyyy',
        'UI_DATE_FORMAT_SAMPLE' => '16/11/1967',

        // various date/time formats used
        'DATE_FORMAT_LONG' => '%A %d %B, %Y',
        'DATE_TIME_FORMAT' => '%d/%m/%Y %H:%M:%S',

        'HTML_CHARSET' => 'iso-8859-1',
        // NOTE: This is *NOT* used in ZenMagick, however zen-cart uses it in some places...
        'HTML_PARAMS' => 'dir="ltr" lang="en"'
    ));

?>
