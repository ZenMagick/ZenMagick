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
 * A central place for all request/session stuff.
 *
 * @author mano
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMRequest extends ZMObject {
    private $controller_;
    private $session_;
    private $parameter_;
    private $categoryPathArray_;
    private $shoppingCart_;


    /**
     * Create new instance.
     *
     * @param array parameter Optional request parameter; if <code>null</code>,
     *  <code>$_GET</code> and <code>$_POST</code> will be used.
     */
    function __construct($parameter=null) {
        parent::__construct();

        if (null != $parameter) {
            $this->parameter_ = $parameter;
        } else {
            $this->parameter_ = array_merge($_POST, $_GET);
        }
        $this->controller_ = null;
        $this->session_ = null;
        $this->categoryPathArray_ = null;
        $this->cart_ = null;
    }


    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    protected static function instance() {
        return ZMObject::instance('Request');
    }


    /**
     * Get the request method.
     *
     * @return string The upper case request method.
     */
    public static function getMethod() { return strtoupper($_SERVER['REQUEST_METHOD']); }

    /**
     * Check for a valid session.
     *
     * @return boolean <code>true</code> if a valid session exists, <code>false</code> if not.
     */
    public static function isValidSession() {
        return ZMRequest::instance()->getSession()->isValid();
    }

    /**
     * Get the current session.
     *
     * @return ZMSession The session.
     */
    public function getSession() { 
        $self = ZMRequest::instance();
        if (!isset($self->session_)) { 
            $self->session_ = ZMLoader::make("Session"); 
        } 

        return $self->session_;
    }

    /**
     * Get the hostname for this request.
     *
     * @return strng The hostname.
     */
    public static function getHostname() { return $_SERVER['HTTP_HOST']; }

    /**
     * Get the full query string.
     *
     * @return string The full query string for this request.
     */
    public static function getQueryString() { return $_SERVER['QUERY_STRING']; }

    /**
     * Get the complete parameter map.
     *
     * @return array Map of all request parameters
     */
    public function getParameterMap() { return ZMRequest::instance()->parameter_; }

    /**
     * Set the parameter map.
     *
     * @param array map Map of all request parameters
     */
    public function setParameterMap($map) { ZMRequest::instance()->parameter_ = $map; }

    /**
     * Get the current shopping cart.
     *
     * @return ZMShoppingCart The current shopping cart (may be empty).
     */
    public function getShoppingCart() { 
        $self = ZMRequest::instance();
        if (null == $self->shoppingCart_) {
            $self->shoppingCart_ = ZMLoader::make('ShoppingCart');
        }

        return $self->shoppingCart_;
    }

    /**
     * Get the current page name; ie the <code>main_page</code> value.
     *
     * @return string The value of the <code>main_page</code> query parameter.
     */
    public function getPageName() { return ZMRequest::instance()->getParameter('main_page'); }

    /**
     * Get the current page index (if available).
     *
     * @return int The current page index (default is 1).
     */
    public function getPageIndex() {  return ZMRequest::instance()->getParameter('page', 1); }

    /**
     * Get the current sort id.
     *
     * @return string The current sort id.
     */
    public function getSortId() {  return ZMRequest::instance()->getParameter('sort_id'); }

    /** 
     * Get the sub page name; this is the contents name for static pages.
     *
     * @return strin The static page contents id.
     */
    public function getSubPageName() { return ZMRequest::instance()->getParameter('cat'); }

    /**
     * Get the product id.
     *
     * @return int The request product id or <code>0</code>.
     */
    public function getProductId() { return (int)ZMRequest::instance()->getParameter('products_id', ZMRequest::instance()->getParameter('productId', 0)); }

    /**
     * Get the language code.
     *
     * <p><strong>NOTE:</strong> This will return only the language code as found in the request. If you 
     * want to find out the session language (ie the language for the current request), use <code>ZMRuntime::getLanguageId()</code>
     * or <code>ZMRuntime::getLanguage()</code>.</p>
     *
     * @return string The language code or <code>null</code>.
     */
    public function getLanguageCode() { return (int)ZMRequest::instance()->getParameter('language'); }

    /**
     * Get the currency code.
     *
     * <p><strong>NOTE:</strong> This will return the currency code as found in the request. If not set,
     * the session currency code will be returned instead. To access the session currency code directly, use 
     * <code>ZMRequest::getSession()->getCurrencyCode()</code>.</p>
     *
     * @return string The currency code or <code>null</code>.
     */
    public function getCurrencyCode() { return ZMRequest::instance()->getParameter('currency', ZMRequest::instance()->getSession()->getCurrencyCode()); }

    /**
     * Get the request model number.
     *
     * @return string The model numner or <code>null</code>.
     */
    public function getModel() { return ZMRequest::instance()->getParameter('model'); }

    /**
     * Get the current category path.
     *
     * @return string The category path value (<code>cPath</code>) or <code>null</code>.
     */
    public function getCategoryPath() { return ZMRequest::instance()->getParameter('cPath', null); }

    /**
     * Get the category path arry.
     *
     * @return array The current category path broken into an array of category ids.
     */
    public function getCategoryPathArray() {
        $path = ZMRequest::instance()->getParameter('cPath');
        $cPath = array();
        if (null !== $path) {
            $path = explode('_', $path);
            foreach ($path as $categoryId) {
                $categoryId = (int)$categoryId;
                if (!in_array($categoryId, $cPath)) {
                    $cPath[] = $categoryId;
                }
            }
        }
        return $cPath;
    }

    /**
     * Set the category path arry.
     *
     * @param array cPath The category path as array.
     */
    public function setCategoryPathArray($cPath) {
        if (is_array($cPath)) {
            ZMRequest::instance()->setParameter('cPath', implode('_', $cPath));
        } else {
            ZMObject::log('invalid cPath: ' . $cPath, ZM_LOG_ERROR);
        }
    }

    /**
     * Get the manufacturer id.
     *
     * @return int The manufacturer id or <code>0</code>.
     */
    public function getManufacturerId() { return ZMRequest::instance()->getParameter('manufacturers_id', 0); }

    /**
     * Get the account id.
     *
     * @return int The account id for the currently logged in user or <code>0</code>.
     */
    public function getAccountId() { return ZMRequest::instance()->getSession()->getAccountId(); }

    /**
     * Get the account.
     *
     * @return ZMAccount The account or <code>null</code>.
     */
    public function getAccount() {
        $accountId = ZMRequest::instance()->getAccountId();
        if (0 == $accountId) {
            return null;
        }

        return ZMAccounts::instance()->getAccountForId($accountId);
    }

    /**
     * Get the current review id.
     *
     * @return int The current review id or <code>0</code>.
     */
    public function getReviewId() { return ZMRequest::instance()->getParameter('reviews_id', 0); }

    /**
     * Get the current order id.
     *
     * @return int The current order id or <code>0</code>.
     */
    public function getOrderId() { return ZMRequest::instance()->getParameter('order_id', 0); }

    /**
     * Returns <code>true</code> if the user is not logged in at all.
     *
     * @return boolean <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    public function isAnonymous() { return ZMRequest::instance()->getSession()->isAnonymous(); }

    /**
     * Returns <code>true</code> if the user is fully registered and logged in.
     *
     * @return boolean <code>true</code> if the current user is fully registered and logged in, <code>false</code> if not.
     */
    public function isRegistered() { return ZMRequest::instance()->getSession()->isRegistered(); }

    /**
     * Returns <code>true</code> if the user is a guest user.
     *
     * @return boolean <code>true</code> if the current user is guest, <code>false</code> if not.
     */
    public function isGuest() { return ZMRequest::instance()->getSession()->isGuest(); }

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
    public function getRequestParameter($name, $default=null) { 
        return ZMRequest::instance()->getParameter($name, $default, true);
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
    public function getParameter($name, $default=null, $sanitize=true) { 
        if (isset(ZMRequest::instance()->parameter_[$name])) {
            return $sanitize ? zm_sanitize(ZMRequest::instance()->parameter_[$name]) : ZMRequest::instance()->parameter_[$name];
        } else {
            return $default;
        }
    }

    /**
     * Allow programmatic manipulation of request parameters.
     *
     * @param string name The paramenter name.
     * @param mixed value The value.
     * @return mixed The previous value or <code>null</code>.
     */
    public function setParameter($name, $value) { 
        $old = null;
        if (isset(ZMRequest::instance()->parameter_[$name])) {
            $old = ZMRequest::instance()->parameter_[$name];
        }
        ZMRequest::instance()->parameter_[$name] = $value;
        return $old;
    }

    /**
     * Get the controller for this request.
     *
     * @return ZMController The current controller or <code>ZMDefaultController</code>.
     */
    public function getController() { 
        if (null === ZMRequest::instance()->controller_) {
            ZMRequest::instance()->controller_ = ZMLoader::make("DefaultController");
        } 
        
        return ZMRequest::instance()->controller_; 
    }

    /**
     * Set the current controller.
     *
     * @param ZMController controller The new controller.
     */
    public function setController($controller) { ZMRequest::instance()->controller_ = $controller; }

    /**
     * Get the current category id.
     *
     * @return int The current category id or <code>0</code>.
     */
    public function getCategoryId() {
        $cPath = ZMRequest::instance()->getCategoryPathArray();

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
    public function isSecure() {
        return 443 == $_SERVER['SERVER_PORT'] || (isset($_SERVER['HTTPS']) && 'on' == strtolower($_SERVER['HTTPS']));
    }

    /**
     * Get the page base url.
     *
     * @return string A base URL for the current request.
     */
    public function getPageBase() {
        $base = null;
        if (!ZMRequest::instance()->isSecure()) { 
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
    public function isAdmin() {
        return defined('IS_ADMIN_FLAG') && constant('IS_ADMIN_FLAG');
    }

}

?>
