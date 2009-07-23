<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * A request wrapper.
 *
 * <p><strong>NOTE:</strong</strong> For the time of transition between static and instance
 * usage of request methods this will have a temp. name of <code>ZMRequest</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 * @version $Id$
 */
class ZMRequest extends ZMObject {
    /** Fired before a redirect. */
    const EVENT_REDIRECT = 'redirect';
    /** 
     * Paramter name containing the request id. 
     *
     * <p>The request/page id determines the page being displayed.</p>
     */
    const REQUEST_ID = 'zmreq';

    private $controller_;
    private $session_;
    private $parameter_;


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
    }


    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get instance.
     *
     * <p>A final straw to get the shared request instance if nothing else is 
     * available.</p>
     */
    public static function instance() {
        return ZMObject::singleton('Request');
    }

    /**
     * Get the request method.
     *
     * @return string The upper case request method.
     */
    public function getMethod() {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Check for a valid session.
     *
     * @return boolean <code>true</code> if a valid session exists, <code>false</code> if not.
     */
    public function isValidSession() {
        return $this->getSession()->isValid();
    }

    /**
     * Get the current session.
     *
     * @return ZMSession The session.
     */
    public function getSession() { 
        if (!isset($this->session_)) { 
            $this->session_ = ZMLoader::make("Session"); 
        } 

        return $this->session_;
    }

    /**
     * Set the current session instance.
     *
     * @param ZMSession session The session.
     */
    public function setSession($session) { 
        $this->session_ = $session;
    }

    /**
     * Get the hostname for this request.
     *
     * @return strng The hostname.
     */
    public function getHostname() {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Get the full query string.
     *
     * @return string The full query string for this request.
     */
    public function getQueryString() {
        return $_SERVER['QUERY_STRING'];
    }

    /**
     * Get the complete parameter map.
     *
     * @param boolean sanitize If <code>true</code>, sanitze value; default is <code>true</code>.
     * @return array Map of all request parameters
     */
    public function getParameterMap($sanitize=true) { 
        $map = array();
        foreach (array_keys($this->parameter_) as $key) {
            // checkbox special case
            if (0 === strpos($key, '_')) {
                $key = substr($key, 1);
            }
            $map[$key] = $this->getParameter($key, null, $sanitize);
        }

        return $map;
    }

    /**
     * Set the parameter map.
     *
     * @param array map Map of all request parameters
     */
    public function setParameterMap($map) {
        $this->parameter_ = $map;
    }

    /**
     * Get the request id.
     *
     * <p>The request id is the main criteria for selecting the controller and view to process this
     * request.</p>
     *
     * @return string The value of the <code>self::REQUEST_ID</code> query parameter.
     */
    public function getRequestId() {
        return $this->getParameter(self::REQUEST_ID);
    }

    /**
     * Set the request id.
     *
     * @param string requestId The new request id.
     */
    public function setRequestId($requestId) {
        $this->setParameter(self::REQUEST_ID, $requestId);
    }

    /**
     * Generic access method for request parameter.
     *
     * <p>This method is evaluating both <code>GET</code> and <code>POST</code> parameter.</p>
     *
     * <p>There is a special case for when a parameter is not found, but _[name] is found. In this
     * case <code>false</code> is returned. This allows to handle checkboxes same as any other form element
     * by adding a hidden field _[name] with the original value.</p>
     *
     * @param string name The paramenter name.
     * @param mixed default An optional default parameter (if not provided, <code>null</code> is used).
     * @param boolean sanitize If <code>true</code>, sanitze value; default is <code>true</code>.
     * @return mixed The parameter value or the default value or <code>null</code>.
     */
    public function getParameter($name, $default=null, $sanitize=true) { 
        if (isset($this->parameter_[$name])) {
            return $sanitize ? ZMSecurityUtils::sanitize($this->parameter_[$name]) : $this->parameter_[$name];
        }

        // special case for checkboxes/radioboxes?
        if (isset($this->parameter_['_'.$name])) {
            // checkbox boolean value 
            return false;
        }

        return $default;
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
        if (isset($this->parameter_[$name])) {
            $old = $this->parameter_[$name];
        }
        $this->parameter_[$name] = $value;
        return $old;
    }

    /**
     * Get the controller for this request.
     *
     * <p>In case the controller is not explicitely set, the method will use the url mapper
     * (<code>ZMUrlMapper::findController()</code>) to determine a controller. This will then
     * be either a configured controller or the default controller.</p>
     *
     * @return ZMController The current controller.
     */
    public function getController() { 
        if (null === $this->controller_) {
            $this->controller_ = ZMUrlMapper::instance()->findController($this->getRequestId());
        }

        return $this->controller_; 
    }

    /**
     * Set the current controller.
     *
     * @param ZMController controller The new controller.
     */
    public function setController($controller) {
        $this->controller_ = $controller;
    }

    /**
     * Checks if the current request is secure or note.
     *
     * @return boolean <code>true</code> if the current request is secure; eg. SSL, <code>false</code> if not.
     */
    public function isSecure() {
        return 443 == $_SERVER['SERVER_PORT'] || (isset($_SERVER['HTTPS']) && ZMLangTools::asBoolean($_SERVER['HTTPS']));
    }

    /**
     * Redirect to the given url.
     *
     * @param string url A fully qualified url.
     * @param int status Optional status; default is <em>302 - FOUND</em>.
     */
    public function redirect($url, $status=302) {
        $url = str_replace('&amp;', '&', $url);
        ZMEvents::instance()->fireEvent($this, self::EVENT_REDIRECT, array('request' => $this, 'url' => $url));
        ZMLogging::instance()->trace('redirect url: ' . $url, ZMLogging::TRACE);
        header('Location: ' . $url, true, $status);
        exit;
    }

}

?>
