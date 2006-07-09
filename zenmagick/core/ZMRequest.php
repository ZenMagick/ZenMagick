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
 */
?>
<?php


/**
 * A central place for all request/session stuff.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMRequest {

    // create new instance
    function ZMRequest() {
        $this->_init_l10n_i18n();
    }

    // create new instance
    function __construct() {
        $this->ZMRequest();
    }

    function __destruct() {
    }


    // getter/setter
    function getLanguageId() { return (int)$_SESSION['languages_id']; }
    function getLanguageName() { return $_SESSION['language']; }
    function getQueryString() { return $_SERVER['QUERY_STRING']; }
    function getCurrencyId() { return $_SESSION['currency']; }
    function getShoppingCart() { return isset($_SESSION['cart']) ? $_SESSION['cart'] : null; }
    function getPageName() { return $_GET['main_page']; }
    function getPageIndex() {  return isset($_GET['page']) ? $_GET['page'] : 1; }
    function getSortOrder() {  return isset($_GET['sort']) ? $_GET['sort'] : null; }
    function getSubPageName() { return isset($_GET['cat']) ? $_GET['cat'] : null; }
    function getProductId() { return isset($_GET['products_id']) ? (int)$_GET['products_id'] : (int)$this->getRequestParameter("productId", 0); }
    function getModel() { return isset($_GET['model']) ? $_GET['model'] : null; }
    function getCategoryPath() { return $this->getRequestParameter('cPath', null); }
    function getCategoryPathArray() { global $cPath_array; return is_array($cPath_array) ? $cPath_array : array(); }
    function getManufacturerId() { return isset($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : null; }
    function getFilterId() { return isset($_GET['filter_id']) ? (int)$_GET['filter_id'] : null; }
    function getAccountId() { return isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : null; }
    function getReviewId() { return isset($_GET['reviews_id']) ? (int)$_GET['reviews_id'] : 0; }
    function getOrderId() { return isset($_GET['order_id']) ? (int)$_GET['order_id'] : null; }
    function isGuest() { return !array_key_exists('customer_id', $_SESSION) || '' == $_SESSION['customer_id']; }
    function getRequestParameter($name, $default=null) { 
        return isset($_GET[$name]) ? zm_stripslashes($_GET[$name]) : (isset($_POST[$name]) ? zm_stripslashes($_POST[$name]) : $default);
    }

    function getCategoryId() {
    global $zm_runtime;
        $categories = $zm_runtime->getCategories();
        $category = $categories->getActiveCategory();
        return null != $category ? $category->getId() : null;
    }

    function getCrumbtrail() {
    global $zm_runtime;
        $crumbtrail = new Crumbtrail($zm_runtime->getDB(), $this->getCategoryPathArray());
        if (null != $this->getManufacturerId()) {
            $crumbtrail->addManufacturer($this->getManufacturerId());
        }
        if (null != $this->getProductId()) {
            $crumbtrail->addProduct($this->getProductId(), $this->getLanguageId());
        }
        return $crumbtrail;
    }


    /**
     * Checks if the current request is secure or note.
     *
     * @return bool <code>true</code> if the current request is secure; eg. SSL, <code>false</code> if not.
     */
    function isSecure() {
        return 443 == $_SERVER['SERVER_PORT'] || (isset($_SERVER['HTTPS']) && 'on' == strtolower($_SERVER['HTTPS']));
    }

    /**
     * Get the page base url.
     *
     * @return string A base URL for the current request.
     */
    function getPageBase() {
        $base = null;
        print_r($_REQUEST);
        if (!$this->isSecure()) { 
            $base = HTTP_SERVER . DIR_WS_CATALOG;
        } else {
            $base = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
        }
         return $base;
    }

    // set up l10n/i18n support
    function _init_l10n_i18n() {
    global $zm_runtime;
        if (!isset($GLOBALS['zm_l10n_text'])) {
            $GLOBALS['zm_l10n_text'] = array();
        }

        // language for this request
        $lang = $this->getLanguageName();

        // check if is already initialised
        if (array_key_exists($lang, $GLOBALS['zm_l10n_text']))
            return;

        $path = $zm_runtime->getThemeLangPath().$lang;
        $includes = zm_find_includes($path);
        if (0 < count($includes)) {
            foreach ($includes as $include) {
                include $include;
            }
        }

        // store language
        $GLOBALS['zm_l10n_text'][$lang] = $zm_l10n_text;
    }

}

?>
