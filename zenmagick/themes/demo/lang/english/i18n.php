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
     * The settings <code>UI_DATE_FORMAT</code> and <code>UI_DATE_FORMAT_SAMPLE</code>
     * are designed to work with a generic zen_raw_date(..) function provided
     * by ZenMagick.
     */

    // locale
    @setlocale(LC_TIME, 'en_US.ISO_8859-1');

    // various date/time settings as in zen-cart
    define('DATE_FORMAT_SHORT', '%d/%m/%Y');
    define('DATE_FORMAT_LONG', '%A %d %B, %Y');
    define('DATE_FORMAT', 'd/m/Y');
    define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

    // format for account dob, advanced search or simply everywhere where zen-cart
    // is using zen_raw_date(..)
    define('UI_DATE_FORMAT', 'dd/mm/yyyy');
    define('UI_DATE_FORMAT_SAMPLE', '16/11/1967');

    // a validation regular expression
    define('UI_DATE_FORMAT_VALIDATION', '[0-3][0-9]/[0-1][0-9]/[1-2][0-9]{3}');

    // HTML i18n settings
    define('HTML_PARAMS','dir="ltr" lang="en"');
    // charset for web pages and emails
    define('HTML_CHARSET', 'iso-8859-1');

?>
