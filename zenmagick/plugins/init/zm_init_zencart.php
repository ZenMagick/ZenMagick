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
 * Init plugin to set up all zen-cart related dependencies.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class zm_init_zencart extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('zen-cart', 'Set up zen-cart dependencies');
        $this->setScope(ZM_SCOPE_STORE);
        $this->setPreferredSortOrder(0);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        //TODO: this shouldn't be necessary (isAdmin)
        if (!function_exists('zen_exit') && !ZMSettings::get('isAdmin')) {
            // general functions
            require(DIR_WS_FUNCTIONS . 'functions_general.php');
            require(DIR_WS_FUNCTIONS . 'html_output.php');
            require(DIR_WS_FUNCTIONS . 'functions_email.php');
            require(DIR_WS_FUNCTIONS . 'functions_ezpages.php');
            include(DIR_WS_MODULES . 'extra_functions.php');

            // specials
            require(DIR_WS_FUNCTIONS . 'whos_online.php');
            require(DIR_WS_FUNCTIONS . 'password_funcs.php');
            require(DIR_WS_FUNCTIONS . 'banner.php');
            require(DIR_WS_FUNCTIONS . 'specials.php');
            require(DIR_WS_FUNCTIONS . 'featured.php');
            require(DIR_WS_FUNCTIONS . 'salemaker.php');

            // classes that might be in session
            require(DIR_WS_CLASSES . 'navigation_history.php');

            // other
            require(DIR_WS_FUNCTIONS . 'sessions.php');
        }
    }

}

?>
