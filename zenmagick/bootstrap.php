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
 * $Id$
 */
?>
<?php
    /**
     * This is an attempt to create an alternative bootstrap sequence to minimize
     * zen-cart dependencies and overhead in ZenMagick.
     *
     * Main motivation right now is to see what sort of performance improvements would be possible
     * to cut out even more zen-cart code during request handling.
     *
     * Using this, index.php could be replaced with something like this:
     *
     * require('zenmagick/bootstrap.php');
     * require('zenmagick/init.php');
     * require('zenmagick/store.php');
     * exit;
     *
     * Please note that this is nowhere near usable (even though the
     * homepage loads fine).
     * There are still plenty of places where zen-cart code is required,
     * in particular the checkout pages all still use zen-cart's page
     * handler code.
     *
     * To make the above request handling work, all init plugins need to be
     * installed and enabled.
     *
     * Possible improvements:
     * - merge referenced zen-cart files where appropriate
     * - copy/move zen-cart files somewhere into core!
     * - modify ZMCoreCompressor to merge bootstrap, init and store into a single index.php!
     */
?>
<?php

    /** set a few defaults and load configuration **/

    define('DEBUG_AUTOLOAD', false);
    define('IS_ADMIN_FLAG', false);

    if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

    define('PAGE_PARSE_START_TIME', microtime());
    @ini_set("arg_separator.output", "&");

    if (file_exists('includes/local/configure.php')) {
        include('includes/local/configure.php');
    }

    if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
        @ini_set('display_errors', '1');
        error_reporting(E_ALL);
    } else {
        error_reporting(E_ALL & ~E_NOTICE);
    }

    if (file_exists('includes/configure.php')) {
        include('includes/configure.php');
    } else {
        die('configuration missing');
    }

    require(DIR_WS_INCLUDES . 'filenames.php');
    require(DIR_WS_INCLUDES . 'database_tables.php');


    /** load extra stuff for zc compatibility **/

    $extra_datafiles = DIR_FS_CATALOG . DIR_WS_INCLUDES . 'extra_datafiles/';

    $extra_files = array();
    if ($dir = @dir($extra_datafiles)) {
        while ($file = $dir->read()) {
            $file = $extra_datafiles . $file;
            if (!is_dir($file)) {
                if (preg_match('/\.php$/', $file) > 0) {
                    $extra_files[] = $file;
                }
            }
        }
        $dir->close();

        if (sizeof($extra_files)) {
            // sort for some reason
            sort($extra_files);
            foreach ($extra_files as $file) {
                include($file);
            }
        }
    }


    /** load some zc classs **/

    require(DIR_WS_CLASSES . 'class.base.php');
    require(DIR_WS_CLASSES . 'cache.php');
    require(DIR_WS_CLASSES . 'language.php');

    $zc_cache = new cache();


    /** connect DB **/

    require(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/query_factory.php');
    $db = new queryFactory();

    if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false)) {
        die("can't connect to db");
    }


    /** load configuration settings from DB **/

    $use_cache = isset($_GET['nocache']) ? false : true;
    $configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                                 from ' . TABLE_CONFIGURATION, '', $use_cache, 150);
    while (!$configuration->EOF) {
        define(strtoupper($configuration->fields['cfgkey']), $configuration->fields['cfgvalue']);
        $configuration->MoveNext();
    }

    $configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                          from ' . TABLE_PRODUCT_TYPE_LAYOUT);
    while (!$configuration->EOF) {
        define(strtoupper($configuration->fields['cfgkey']), $configuration->fields['cfgvalue']);
        $configuration->movenext();
    }

    /** load predefined queries **/
    if (file_exists(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php')) {
        include(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php');
    }

?>
