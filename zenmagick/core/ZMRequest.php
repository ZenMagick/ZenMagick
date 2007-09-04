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
    var $session_;
    var $request_;


    /**
     * Default c'tor.
     *
     * @param array The request; optional, if <code>null</code>,
     *  <code>$_GET</code> and <code>$_POST</code> will be used.
     */
    function ZMRequest($request=null) {
        parent::__construct();

        $this->controller_ = null;
        $this->session_ = new ZMSession();
        if (null != $request) {
            $this->request_ = $request;
        } else {
            $this->request_ = array_merge($_POST, $_GET);
        }
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
     * Get the request method.
     *
     * @return string The upper case request method.
     */
    function getMethod() { return strtoupper($_SERVER['REQUEST_METHOD']); }

    /**
     * Check for a valid session.
     *
     * @return boolean <code>true</code> if a valid session exists, <code>false</code> if not.
     */
    function isValidSession() {
        return $this->session_->isValid();
    }

    /**
     * Get the current session.
     *
     * @return ZMSession The session.
     */
    function &getSession() { return $this->session_; }

    /**
     * Get the full query string.
     *
     * @return string The full query string for this request.
     */
    function getQueryString() { return $_SERVER['QUERY_STRING']; }

    /**
     * Get the complete parameter map.
     *
     * @return array Map of all request parameters
     */
    function getParameterMap() { return $this->request_; }

    /**
     * Get the current shopping cart.
     *
     * @return ZMShoppingCart The current shopping cart (may be empty).
     */
    function &getShoppingCart() { return $this->session_->getShoppingCart(); }

    /**
     * Get the current page name; ie the <code>main_page</code> value.
     *
     * @return string The value of the <code>main_page</code> query parameter.
     */
    function getPageName() { return $this->getParameter('main_page'); }

    /**
     * Get the current page index (if available).
     *
     * @return int The current page index (default is 1).
     */
    function getPageIndex() {  return $this->getParameter('page', 1); }

    /**
     * Get the current sort id.
     *
     * @return string The current sort id.
     */
    function getSortId() {  return $this->getParameter('sort_id'); }

    /** 
     * Get the sub page name; this is the contents name for static pages.
     *
     * @return strin The static page contents id.
     */
    function getSubPageName() { return $this->getParameter('cat'); }

    /**
     * Get the product id.
     *
     * @return int The request product id or <code>0</code>.
     */
    function getProductId() { return (int)$this->getParameter('products_id', $this->getParameter('productId', 0)); }

    /**
     * Get the request model number.
     *
     * @return string The model numner or <code>null</code>.
     */
    function getModel() { return $this->getParameter('model'); }

    /**
     * Get the current category path.
     *
     * @return string The category path value (<code>cPath</code>) or <code>null</code>.
     */
    function getCategoryPath() { return $this->getParameter('cPath', null); }

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
    function getManufacturerId() { return $this->getParameter('manufacturers_id'); }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    function getAccountId() { return $this->session_->getAccountId(); }

    /**
     * Get the account.
     *
     * @return ZMAccount The account or <code>null</code>.
     */
    function &getAccount() {
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
    function getReviewId() { return $this->getParameter('reviews_id', 0); }

    /**
     * Get the current order id.
     *
     * @return int The current order id or <code>0</code>.
     */
    function getOrderId() { return $this->getParameter('order_id', 0); }

    /**
     * Returns <code>true</code> if the user is not logged in at all.
     *
     * @return boolean <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    function isAnonymous() { return $this->session_->isAnonymous(); }

    /**
     * Returns <code>true</code> if the user is fully registered and logged in.
     *
     * @return boolean <code>true</code> if the current user is fully registered and logged in, <code>false</code> if not.
     */
    function isRegistered() { return $this->session_->isRegistered(); }

    /**
     * Returns <code>true</code> if the user is a guest user.
     *
     * @return boolean <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    function isGuest() { return $this->session_->isGuest(); }

    /**
     * Generic access method for request parameter.
     *
     * <p>This method is evaluating both <code>GET</code> and <code>POST</code> parameter.</p>
     *
     * @param string name The paramenter name.
     * @param mixed default An optional default parameter (if not provided, <code>null</code> is used).
     * @return string The parameter value or the default value or <code>null</code>.
     * @deprecated use getParameter() instead
     */
    function getRequestParameter($name, $default=null) { 
        return $this->getParameter($name, $default, true);
    }

    /**
     * Generic access method for request parameter.
     *
     * <p>This method is evaluating both <code>GET</code> and <code>POST</code> parameter.</p>
     *
     * @param string name The paramenter name.
     * @param mixed default An optional default parameter (if not provided, <code>null</code> is used).
     * @param boolean sanitize If <code>true</code>, sanitze value; default is <code>true</code>.
     * @return mixed The parameter value or the default value or <code>null</code>.
     */
    function getParameter($name, $default=null, $sanitize=true) { 
        if (isset($this->request_[$name])) {
            return $sanitize ? zm_sanitize($this->request_[$name]) : $this->request_[$name];
        } else {
            return $default;
        }
    }

    /**
     * Get the controller for this request.
     *
     * @return ZMController The current controller or <code>ZMDefaultController</code>.
     */
    function &getController() { if (null === $this->controller_) {$this->controller_ =& $this->create("DefaultController"); } return $this->controller_; }

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
     * @return boolean <code>true</code> if the current request is secure; eg. SSL, <code>false</code> if not.
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

    /**
     * Check if we are running as admin.
     *
     * @return boolean <code>true</code> if code execution is in the context of an admin page,
     *  <code>false</code> if not.
     */
    function isAdmin() {
        return defined('IS_ADMIN_FLAG') && constant('IS_ADMIN_FLAG');
    }

}

?>
