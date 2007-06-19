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
     * <strong>Experimental:</strong> Call zen-cart page controller code.
     *
     * <p>All variables created by zen-cart code that the caller wants to
     * access need to be included in the globals list in order to declare
     * the global...</p>
     *
     * @package net.radebatz.zenmagick.misc
     * @param string page The page name (main_page).
     * @param mixed globals Either an array or comma separated list of names to be treated as globals.
     */
    function zm_call_zc_page($page, $globals=null) {
        // fake global
        foreach ($GLOBALS as $name => $instance) {
            if (!zm_starts_with($name, "_")) {
                $$name = $instance;
            }
        }
        if (null != $globals) {
            if (!is_array($globals)) {
                $globals = explode(':', $globals);
            }
            $cmd = 'global ';
            $first = true;
            foreach ($globals as $global) {
                if (!$first) { $cmd .= ', '; }
                $cmd .= '$'.$global;
            }
            $cmd .= ';';
            eval($cmd);
        }


        if (defined('ZM_EXTERNAL_CALL')) {
            // make sure we are in the right working directory...
            chdir($zm_ext_zenroot);
        }

        foreach ($autoLoadConfig as $actionPoint => $row) {
            foreach($row as $key => $entry) {
                // re-run init scripts
                if (!zm_is_in_array($entry['autoType'], 'init_script')) {
                    unset($autoLoadConfig[$actionPoint][$key]);
                    continue;
                }
                // but not all of them...
                if (!zm_is_in_array($entry['loadFile'], 'init_cart_handler.php')) {
                    unset($autoLoadConfig[$actionPoint][$key]);
                    continue;
                }
            }
        }

        // call cart handler
        require('includes/autoload_func.php');
        $code_page_directory = DIR_WS_MODULES . 'pages/' . $page;

        // from index.php
        $language_page_directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/';
        $directory_array = $template->get_template_part($code_page_directory, '/^header_php/');
        foreach ($directory_array as $value) {
            require($code_page_directory . '/' . $value);
        }
        require($template->get_template_dir('main_template_vars.php', DIR_WS_TEMPLATE, $current_page_base,'common'). '/main_template_vars.php');

        // and back...
        if (defined('ZM_EXTERNAL_CALL')) {
            // make sure we are in the right working directory...
            chdir($zm__ext_cwd);
        }
    }


    /**
     * A few wrapper around common zen methods and other stuff.
     * This is not to claim functionallity, but to keep track of zen-cart
     * dependencies.
     * Also a good reference about dependencies.
     *
     * TODO: convert/integrate db related stuff into model/DAO architecture
     */

    // $currencies might be used by the templates further down the track
    $zen_currencies = $currencies;
    function zm_format_currency($amount, $echo=true) {
    global $zen_currencies;
        $price = $zen_currencies->format($amount);

        if ($echo) echo $price;
        return $price;
    }
    function zm_add_tax($value, $rate) { return zen_add_tax($value, $rate); }

    // simple request scope cache
    $_ZM_CACHE_TAX_RATES = array();
    function zm_get_tax_rate($id) {
    global $_ZM_CACHE_TAX_RATES;
        if (!array_key_exists($id, $_ZM_CACHE_TAX_RATES)) {
            $_ZM_CACHE_TAX_RATES[$id] = zen_get_tax_rate($id);
        }
        return $_ZM_CACHE_TAX_RATES[$id];
    }
    function zm_get_attributes_price_final($arg1, $args2, $arg3, $arg4) {
        return zen_get_attributes_price_final($arg1, $args2, $arg3, $arg4);
    }
    function zm_get_attributes_price_final_onetime($arg1, $args2, $arg3) {
        return zen_get_attributes_price_final_onetime($arg1, $args2, $arg3);
    }
    function zm_has_product_attributes_values($productId) { return zen_has_product_attributes_values($productId); }
    function zm_get_discount_calc($arg1, $args2, $arg3) { return zen_get_discount_calc($arg1, $args2, $arg3); }
    function zm_get_info_page($value) { return zen_get_info_page($value); }
    function zm_check_stock($id, $qty) { return zen_check_stock($id, $qty); }
    function zm_date_short($date, $echo=true) { if($echo) echo zen_date_short($date); return zen_date_short($date); }
    function zm_image($src, $alt='', $width='', $height='', $parameters='') {
        return zen_image(DIR_WS_TEMPLATE_IMAGES.$src, $alt, $width, $height, $parameters);
    }
    function zm_pimage($src, $alt='', $width='', $height='', $parameters='') {
        return zen_image(DIR_WS_IMAGES.$src, $alt, $width, $height, $parameters);
    }

    function zm_clear_session() {
        @zen_session_destroy();
        // clear session
        unset($_SESSION['customer_id']);
        // clear cart
        unset($_SESSION['cart']);
    }

    function zm_field_length($context, $field, $max=40, $echo=true) {
        $length = zen_field_length($context, $field);
        $html = '';
        switch (true) {
            case ($length > $max):
                $html = 'size="' . ($max+1) . '" maxlength="' . $length . '"';
                break;
            case (0 == $max):
                $html = '" maxlength="' . $length . '"';
                break;
            default:
                $html = 'size="' . ($length+1) . '" maxlength="' . $length . '"';
                break;
        }

        if ($echo) echo $html;
        return $html;
    }

    // get online counter
    function zm_get_online_counts() {
    global $db;

        // expire
        $timeAgo = (time() - 1200);
        $sql = "delete from " . TABLE_WHOS_ONLINE . "
                where time_last_click < :timeAgo";
        $sql = $db->bindVars($sql, ":timeAgo", $timeAgo, 'integer');
        $db->Execute($sql);

        $sql = "select customer_id from " . TABLE_WHOS_ONLINE;
        $results = $db->Execute($sql);
        $guests = 0;
        $members = 0;
        while (!$results->EOF) {
            if (!$results->fields['customer_id'] == 0) $members++;
            if ($results->fields['customer_id'] == 0) $guests++;
            $results->MoveNext();
        }

        return array(($guests+$members), $guests, $members);
    }

?>
