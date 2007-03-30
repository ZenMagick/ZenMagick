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
class ZMRequest extends ZMObject {
    var $controller_;


    /**
     * Default c'tor.
     */
    function ZMRequest() {
        parent::__construct();

        $this->controller_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMRequest();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the full query string.
     *
     * @return string The full query string for this request.
     */
    function getQueryString() { return $_SERVER['QUERY_STRING']; }

    /**
     * Get the current shopping cart.
     *
     * @return ZMShoppingCart The current shopping cart (may be empty).
     */
    function getShoppingCart() { return isset($_SESSION['cart']) ? $_SESSION['cart'] : null; }

    /**
     * Get the current page name; ie the <code>main_page</code> value.
     *
     * @return string The value of the <code>main_page</code> query parameter.
     */
    function getPageName() { return $_GET['main_page']; }

    /**
     * Get the current page index (if available).
     *
     * @return int The current page index (default is 1).
     */
    function getPageIndex() {  return isset($_GET['page']) ? $_GET['page'] : 1; }

    /**
     * Get the current sort id.
     *
     * @return string The current sort id.
     */
    function getSortId() {  return isset($_GET['sort_id']) ? $_GET['sort_id'] : null; }

    /** 
     * Get the sub page name; this is the contents name for static pages.
     *
     * @return strin The static page contents id.
     */
    function getSubPageName() { return isset($_GET['cat']) ? $_GET['cat'] : null; }

    /**
     * Get the product id.
     *
     * @return int The request product id or <code>0</code>.
     */
    function getProductId() { return isset($_GET['products_id']) ? (int)$_GET['products_id'] : (int)$this->getRequestParameter("productId", 0); }

    /**
     * Get the request model number.
     *
     * @return string The model numner or <code>null</code>.
     */
    function getModel() { return isset($_GET['model']) ? $_GET['model'] : null; }

    /**
     * Get the current category path.
     *
     * @return string The category path value (<code>cPath</code>) or <code>null</code>.
     */
    function getCategoryPath() { return $this->getRequestParameter('cPath', null); }

    /**
     * Get the category path arry.
     *
     * @return array The current category path broken into an array of category ids.
     */
    function getCategoryPathArray() { global $cPath_array; return is_array($cPath_array) ? $cPath_array : array(); }

    /**
     * Get the manufacturer id.
     *
     * @return int The manufacturer id or <code>0</code>.
     */
    function getManufacturerId() { return isset($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : null; }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    function getAccountId() { return isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0; }

    /**
     * Get the account.
     *
     * @return ZMAccount The account or <code>null</code>.
     */
    function getAccount() {
    global $zm_accounts;
        
        $accountId = $this->getAccountId();
        if (0 == $accountId) {
            return null;
        }

        return $zm_accounts->getAccountForId($accountId);
    }

    /**
     * Get the current review id.
     *
     * @return int The current review id or <code>0</code>.
     */
    function getReviewId() { return isset($_GET['reviews_id']) ? (int)$_GET['reviews_id'] : 0; }

    /**
     * Get the current order id.
     *
     * @return int The current order id or <code>0</code>.
     */
    function getOrderId() { return isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0; }

    /**
     * Returns <code>true</code> if the user is not logged in.
     *
     * @return bool <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    function isGuest() { return !array_key_exists('customer_id', $_SESSION) || '' == $_SESSION['customer_id']; }

    /**
     * Generic access method for request parameter.
     *
     * <p>This method is evaluating both <code>GET</code> and <code>POST</code> parameter.</p>
     *
     * @param string name The paramenter name.
     * @param mixed default An optional default parameter (if not provided, <code>null</code> is used).
     * @return string The parameter value or the default value or <code>null</code>.
     */
    function getRequestParameter($name, $default=null) { 
        return isset($_GET[$name]) ? zm_stripslashes($_GET[$name]) : (isset($_POST[$name]) ? zm_stripslashes($_POST[$name]) : $default);
    }

    /**
     * Get the controller for this request.
     *
     * @return ZMController The current controller or <code>ZMDefaultController</code>.
     */
    function getController() { if (null === $this->controller_) {$this->controller_ =& $this->create("DefaultController"); } return $this->controller_; }

    /**
     * Set the current controller.
     *
     * @param ZMController controller The new controller.
     */
    function setController(&$controller) { $this->controller_ =& $controller; }

    /**
     * Get the current category id.
     *
     * @return int The current category id or <code>0</code>.
     */
    function getCategoryId() {
        $cPath = $this->getCategoryPathArray();

        if (0 < count($cPath)) {
            return end($cPath);
        }

        return 0;
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
        if (!$this->isSecure()) { 
            $base = HTTP_SERVER . DIR_WS_CATALOG;
        } else {
            $base = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
        }
         return $base;
    }

}

?>
