<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2007 ZenMagick
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
 * $Id: external.php,v 1.7 2007/06/02 09:42:33 radebatz Exp $
 */
?>
<?php

    // mark external
    define('ZM_EXTERNAL_CALL', true);

    // get current dir
    $zm__ext_cwd = getcwd();

    // figure out the zen-cart root
    $zm_ext_zenroot = dirname(dirname(__FILE__));
    if (false === $zm_ext_zenroot) {
        $zm_ext_zenroot = str_replace($_SERVER['SCRIPT_NAME'],'', $_SERVER['SCRIPT_FILENAME']);
    }

    // change to zenroot
    chdir($zm_ext_zenroot);
    define('STORE_SESSIONS', ''); // leave empty to avoid session handling errors using $db
    require('includes/application_top.php');

    // change back
    chdir($zm__ext_cwd);

?>
