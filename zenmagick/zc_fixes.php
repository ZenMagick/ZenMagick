<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
     * Simple function to check if we need zen-cart...
     */
    function _zm_needs_zc() {
        $pageName = ZMRequest::getPageName();
        return (ZMRequest::isCheckout() && 'checkout_shipping_address' != $pageName && 'checkout_payment_address' != $pageName) 
            || ZMTools::inArray($pageName, 'advanced_search_result');
    }

    // skip more zc request handling
    if (!_zm_needs_zc() && ZMSettings::get('isEnableZMThemes')) {
        $code_page_directory = 'zenmagick';
    }

    ZMEvents::instance()->attach(ZMLoader::make("EventFixes"));

    // simulate the number of uploads parameter for add to cart
    if ('add_product' == ZMRequest::getParameter('action')) {
        $uploads = 0;
        foreach (ZMRequest::getParameterMap() as $name => $value) {
            if (ZMTools::startsWith($name, ZMSettings::get('uploadOptionPrefix'))) {
                ++$uploads;
            }
        }
        $_GET['number_of_uploads'] = $uploads;
    }

    // make action work with zen-cart cart and checkout code
    if (isset($_POST['action']) && !isset($_GET['action'])) {
        $_GET['action'] = $_POST['action'];
    }

    // fix default dates in advanced search (eventually, this should be done by the model or controller)
    if ('advanced_search_result' == ZMRequest::getPageName()) {
        if ($_GET['dfrom'] == UI_DATE_FORMAT) {
            $_GET['dfrom'] = '';
        }
        if ($_GET['dto'] == UI_DATE_FORMAT) {
            $_GET['dto'] = '';
        }
    }

    // used by some zen-cart validation code
    if (defined('UI_DATE_FORMAT')) {
        define('DOB_FORMAT_STRING', UI_DATE_FORMAT);
    }

    // do not check for valid product id
    $_SESSION['check_valid'] = 'false';

?>
