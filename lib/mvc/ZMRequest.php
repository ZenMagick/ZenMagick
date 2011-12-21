<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\logging\Logging;
use zenmagick\base\events\VetoableEvent;
use zenmagick\http\routing\loader\YamlLoader;


/**
 * A request wrapper.
 *
 * <p><strong>NOTE:</strong</strong> For the time of transition between static and instance
 * usage of request methods this will have a temp. name of <code>ZMRequest</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc
 */
class ZMRequest extends ZMObject {
    /**
     * Default paramter name containing the request id.
     *
     * <p>Will be used if the 'zenmagick.http.request.idName' is not set.</p>
     */
    const DEFAULT_REQUEST_ID = 'rid';

    /**
     * Name of the session token form field and also the name in the session.
     */
    const SESSION_TOKEN_NAME = 'stoken';

    private $controller_;
    private $toolbox_;
    private $parameter_;
    private $method_;
    private $router_;


    /**
     * Create new instance.
     *
     * @param array parameter Optional request parameter; if <code>null</code>,
     *  <code>$_GET</code> and <code>$_POST</code> will be used.
     */
    public function __construct($parameter=null) {
        parent::__construct();

        if (null != $parameter) {
            $this->parameter_ = $parameter;
        } else {
            $this->parameter_ = array_merge($_POST, $_GET);
        }

        if (\ZMLangUtils::isEmpty($this->getRequestId())) {
            // empty string is not null!
            $this->setRequestId(null);
        }

        $this->setMethod(array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : 'GET');
        $this->controller_ = null;
        $this->toolbox_ = null;
        $scheme = $this->isSecure() ? 'https' : 'http';
        // empty router
        $requestContext = new RequestContext($this->getContext(), $this->getMethod(), $this->getHostname(), $scheme);
        $options = array('generator_class' => 'zenmagick\\http\\routing\\generator\\UrlGenerator');
        $this->router_ = new Router(new YamlLoader(), '', $options, $requestContext);
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
        return Runtime::getContainer()->getService('request');
    }


    /**
     * Get the router.
     *
     * @return Router The router.
     */
    public function getRouter() {
        return $this->router_;
    }

    /**
     * Get router match for this request.
     *
     * @return array The match or <code>null</code>.
     */
    public function getRouterMatch() {
        if (!Runtime::getSettings()->get('zenmagick.http.routing.enabled', false)) {
            return null;
        }
        // try router first
        $routerMatch = null;
        try {
            // XXX: should this be handled by the router??
            $uri = preg_replace('#^'.$this->getContext().'#', '', $this->getUri());
            $routerMatch = $this->router_->match($uri);
        } catch (Exception $e) {
            Runtime::getLogging()->dump($e, 'no route found', Logging::TRACE);
        }
        return $routerMatch;
    }

    /**
     * Check if this request is an Ajax request.
     *
     * <p>This default implementation will check for a 'X-Requested-With' header. Subclasses are free to
     * extend and override this method for custom Ajax detecting.</p>
     *
     * @return boolean <code>true</code> if this request is considered an Ajax request.
     */
    public function isAjax() {
        $headers = ZMNetUtils::getAllHeaders();
        $ajax = $this->getParameter('ajax', null);
        return $ajax != null ? ZMLangUtils::asBoolean($ajax) : (array_key_exists('X-Requested-With', $headers) && 'XMLHttpRequest' == $headers['X-Requested-With']);
    }

    /**
     * Get a list of <code>zenmagick\htt\request\rewriter\UrlRewriter</code> instances.
     *
     * <p>Instances are looked up in the container with a tag of <em>zenmagick.http.request.rewriter</em>.</p>
     *
     * @return array List of <code>zenmagick\htt\request\rewriter\UrlRewriter</code> instances.
     */
    public function getUrlRewriter() {
        $urlRewriter = array();
        foreach ($this->container->findTaggedServiceIds('zenmagick.http.request.rewriter') as $id => $args) {
            $urlRewriter[] = $this->container->get($id);
        }

        return array_reverse($urlRewriter);
    }

    /**
     * Create a URL.
     *
     * <p>Mother of all URL related methods.</p>
     *
     * <p>If the <code>requestId</code> parameter is <code>null</code>, the current requestId will be
     * used. The provided parameter(s) will be merged into the current query string.</p>
     *
     * <p>If the <code>params</code> parameter is <code>null</code>, all parameters of the
     * current request will be added.</p>
     *
     * <p>This default implementation relies on at least a single (default) SEO rewriter being configured.</p>
     *
     * @param string requestId The request id; default is <code>null</code> to use the value of the current request.
     * @param string params Query string style parameter; if <code>null</code> add all current parameters; default is an empty string for none.
     * @param boolean secure Flag indicating whether to create a secure or non secure URL; default is <code>false</code>.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=false) {
        // custom params handling
        if (null === $params) {
            // if requestId null, keep current and also current params
            $query = $this->getParameterMap();
            unset($query[$this->getRequestIdKey()]);
            unset($query[$this->getSession()->getName()]);
            if (null != $params) {
                parse_str($params, $arr);
                $query = array_merge($query, $arr);
            }
            // rebuild
            $params = array();
            foreach ($query as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $params[] = $name.'[]='.$subValue;
                    }
                } else {
                    $params[] = $name.'='.$value;
                }
            }
            $params = implode('&', $params);
        }
        // default to current requestId
        $requestId = $requestId === null ? $this->getRequestId() : $requestId;

        // adjust according to settings
        $settingService = Runtime::getSettings();
        if ($settingService->get('zenmagick.http.request.secure', true)) {
            // check if always secure
            $secure = $settingService->get('zenmagick.http.request.allSecure', false) || $secure;
        } else {
            // disabled
            $secure = false;
        }

        // delegate generation to SEO rewriters
        $args = array('requestId' => $requestId, 'params' => $params, 'secure' => $secure);
        foreach ($this->getUrlRewriter() as $rewriter) {
            if (null != ($rewrittenUrl = $rewriter->rewrite($this, $args))) {
                return $rewrittenUrl;
            }
        }

        Runtime::getLogging()->trace('unresolved URL: '.$requestId);
        return null;
    }

    /**
     * Decode a (potentially) rewritten request.
     */
    public function urlDecode() {
        // traditional ZenMagick routing
        foreach ($this->getUrlRewriter() as $rewriter) {
            if ($rewriter->decode($this)) {
                break;
            }
        }
        // XXX: TODO: remove eventually
        global $current_page_base;
        if (!isset($current_page_base)) { // don't override the one set by zencart
            $current_page_base = $this->getRequestId();
        }
    }

    /**
     * Convert a given relative URL into an absolute one.
     *
     * @param string url The (relative) URL to convert.
     * @param boolean full Set to true to create a full URL incl. the protocol, hostname, port, etc.; default is <code>false</code>.
     * @param boolean secure Set to true to force a secure URL; default is <code>false</code>.
     * @return string The absolute URL.
     */
    public function absoluteURL($url, $full=false, $secure=false) {
        $url = (!empty($url) && ('/' == $url[0] || false !== strpos($url, '://'))) ? $url : $this->getContext().'/'.$url;

        if ($full || ($secure && !$this->isSecure())) {
            // full requested or we need a full URL to ensure it will be secure
            $isSecure = ($this->isSecure() || $secure);
            $scheme = ($this->isSecure() || $secure) ? 'https://' : 'http://';
            $host = $this->getHostname();
            $port = $this->getPort();
            if ('80' == $port && !$this->isSecure() || '443' == $port && $this->isSecure()) {
                $port = '';
            } else {
                $port = ':'.$port;
            }

            $url = $scheme.$host.$port.$url;
        }

        return $url;
    }

    /**
     * Get the user (if any) for authentication.
     *
     * <p>Creation of the user object is delegated to the configured <code>zenmagick\http\session\UserFactory</code> instance.
     * The factory may be configured as bean defintion via the setting 'zenmagick.mvc.session.userFactory'.</p>
     *
     * @return mixed A user/credentials object. Default is <code>null</code>.
     */
    public function getUser() {
        if ($this->container->has('userFactory') && null != ($userFactory = $this->container->get('userFactory'))) {
            return $userFactory->getUser($this);
        }

        return null;
    }

    /**
     * Get the request method.
     *
     * @return string The (upper case) request method.
     */
    public function getMethod() {
        return $this->method_;
    }

    /**
     * Set the request method.
     *
     * @param string method The request method.
     */
    public function setMethod($method) {
        $this->method_ = strtoupper($method);
    }

    /**
     * Get the current session.
     *
     * @return zenmagick\http\session\Session The session.
     */
    public function getSession() {
        return $this->container->get("session");
    }

    /**
     * Get the hostname for this request.
     *
     * @return string The hostname or <code>null</code> for <em>CL</code> calls.
     */
    public function getHostname() {
        return array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : null;
    }

    /**
     * Get the port for this request.
     *
     * @return string The port or <code>null</code> for <em>CL</code> calls.
     */
    public function getPort() {
        return array_key_exists('SERVER_PORT', $_SERVER) ? $_SERVER['SERVER_PORT'] : null;
    }

    /**
     * Get the full query string.
     *
     * @return string The full query string for this request.
     */
    public function getQueryString() {
        return array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : '';
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
     * Get the name of the request parameter that contains the request id.
     *
     * @return string The request id key.
     */
    public function getRequestIdKey() {
        return \ZMSettings::get('zenmagick.http.request.idName', self::DEFAULT_REQUEST_ID);
    }

    /**
     * Get the request id.
     *
     * <p>The request id is the main criteria for selecting the controller and view to process this
     * request.</p>
     *
     * @return string The request id of this request.
     */
    public function getRequestId() {
        return $this->getParameter($this->getRequestIdKey(), 'index');
    }

    /**
     * Set the request id.
     *
     * @param string requestId The new request id.
     */
    public function setRequestId($requestId) {
        $this->setParameter($this->getRequestIdKey(), $requestId);
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
            return $sanitize ? \ZMSecurityUtils::sanitize($this->parameter_[$name]) : $this->parameter_[$name];
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
     * (<code>ZMUrlManager::instance()->findController()</code>) to determine a controller. This will then
     * be either a configured controller or the default controller.</p>
     *
     * @return ZMController The current controller.
     */
    public function getController() {
        if (null === $this->controller_) {
            // try router first
            $routerMatch = $this->getRouterMatch();
            if (null !== $routerMatch) {
                // class:method ?
                $token = explode(':', $routerMatch['_controller']);
                if (1 == count($token)) {
                    // expect a ZMController instance with traditional processing
                    $this->controller_ = Beans::getBean($routerMatch['_controller']);
                } else {
                    // wrap to allow custom method with variable parameters
                    // TODO: remove once all controller use ype hints for $request
                    if (!array_key_exists('request', $routerMatch)) {
                        // allow $request as mappable parameter too
                        $routerMatch['request'] = $this;
                    }
                    $this->controller_ = new ZMRoutingController(Beans::getBean($token[0]), $token[1], $routerMatch);
                    $this->controller_->setContainer($this->container);
                }
            } else {
                $this->controller_ = \ZMUrlManager::instance()->findController($this->getRequestId());
            }
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
        return (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT']) ||
               (isset($_SERVER['HTTPS']) && \ZMLangUtils::asBoolean($_SERVER['HTTPS'])) ||
               (isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false) ||
               (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false || strpos(strtolower($_SERVER['HTTP_X_FORWARDED_HOST']), $this->getHostname()) !== false)) ||
               (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:') ||
               (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && (\ZMLangUtils::asBoolean($_SERVER['HTTP_X_FORWARDED_SSL']))) ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'ssl' || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) ||
               (isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] != '') ||
               (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443');
    }

    /**
     * Redirect to the given url.
     *
     * @param string url A fully qualified url.
     * @param int status Optional status; default is <em>302 - FOUND</em>.
     */
    public function redirect($url, $status=302) {
        $url = str_replace('&amp;', '&', $url);
        $event = new VetoableEvent($this, array('request' => $this, 'url' => $url));
        Runtime::getEventDispatcher()->dispatch('redirect', $event);
        Runtime::getLogging()->trace(sprintf('redirect url: "%s"; canceled: %s', $url, ($event->isCanceled() ? 'true' : 'false')), Logging::TRACE);
        if ($event->isCanceled()) {
            return;
        }
        $this->container->get('messageService')->saveMessages($this->getSession());
        $this->closeSession();
        if (!empty($status)) {
            header('Location: ' . $url, true, $status);
        } else {
            header('Location: ' . $url, true);
        }
        exit;
    }

    /**
     * Get the toolbox for this request.
     *
     * @return ZMToolbox A toolbox instance.
     */
    public function getToolbox() {
        if (null == $this->toolbox_) {
            $this->toolbox_ = $this->container->get('ZMToolbox');
            $this->toolbox_->setRequest($this);
        }

        return $this->toolbox_;
    }

    /**
     * Get the URL context for this request.
     *
     * @return string The URL context.
     */
    public function getContext() {
        $context = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        return '/' == $context ? '' : $context;
    }

    /**
     * Get the base url.
     *
     * <p>This will return the absolute url for the current request context.</p>
     *
     * @return string A base URL for the current request.
     */
    public function getBaseUrl() {
        return $this->absoluteUrl('', true);
    }

    /**
     * Get the page base url.
     *
     * @return string A base URL for the current request.
     * @deprecated use getBaseUrl() instead.
     */
    public function getPageBase() {
        return $this->getBaseUrl();
    }

    /**
     * Get the document root path.
     *
     * @return string The document root.
     */
    public function getDocRoot() {
        if (!array_key_exists('DOCUMENT_ROOT', $_SERVER) || empty($_SERVER['DOCUMENT_ROOT']) || 0 !== strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = str_replace(DIRECTORY_SEPARATOR, '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
        } else {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        return $docRoot;
    }

    /**
     * Get the uri for this request.
     *
     * @return string The URI.
     */
    public function getUri() {
        $uri = $_SERVER['REQUEST_URI'];
        return $uri;
    }

    /**
     * Get the front controller name.
     *
     * @return string The name of the main <em>.php</em> file.
     */
    public function getFrontController() {
        if (false !== ($components = parse_url($_SERVER['SCRIPT_NAME']))) {
            return basename($components['path']);
        }
        return 'index.php';
    }

    /**
     * Get the template path.
     *
     * @return string The path.
     */
    public function getTemplatePath() {
        return Runtime::getApplicationPath().'templates'.DIRECTORY_SEPARATOR;
    }

    /**
     * Get the web path.
     *
     * @return string The path.
     */
    public function getWebPath() {
        return Runtime::getApplicationPath().'web'.DIRECTORY_SEPARATOR;
    }

    /**
     * Validate session token.
     *
     * @return boolean <code>true</code> in case the session token is valid.
     */
    public function validateSessionToken() {
        $valid = true;
        if (\ZMLangUtils::inArray($this->getRequestId(), \ZMSettings::get('zenmagick.mvc.html.tokenSecuredForms'))) {
            $valid = false;
            if (null != ($token = $this->getParameter(self::SESSION_TOKEN_NAME))) {
                $valid = $this->getSession()->getToken() == $token;
            }
        }

        return $valid;
    }

    /**
     * Save this request as follow up URL.
     *
     * <p>Typically this happends when a request is received without valid authority.
     * The saved URL will be forwarded to, once permissions is gained (user logged in).</p>
     */
    public function saveFollowUpUrl() {
        $params = $this->getParameterMap();
        $ridKey = $this->getRequestIdKey();
        if (array_key_exists($ridKey, $params)) {
            unset($params[$ridKey]);
        }

        $data = array('requestId' => $this->getRequestId(), 'params' => $params, 'secure' => $this->isSecure());
        $this->getSession()->setValue('followUpUrl', $data, 'zenmagick.mvc');
    }

    /**
     * Check if a follow up url exists that should be loaded (after a login).
     *
     * @param boolean clear Optional flag to keep or clear the follow up url; default is <code>true</code> to clear.
     * @return string The url to go to or <code>null</code>.
     */
    public function getFollowUpUrl($clear=true) {
        if (null != ($data = $this->getSession()->getValue('followUpUrl', 'zenmagick.mvc'))) {
            $params = array();
            foreach ($data['params'] as $key => $value) {
                $params[] = $key.'='.$value;
            }
            if ($clear) {
                $this->getSession()->setValue('followUpUrl', null, 'zenmagick.mvc');
            }
            return $this->url($data['requestId'], implode('&', $params), $data['secure']);
        }

        return null;
    }

    /**
     * Get the protocol used.
     *
     * @return string The protocol string.
     */
    public function getProtocol() {
        $protocol = $_SERVER["SERVER_PROTOCOL"];
	      if ('HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol) {
		        $protocol = 'HTTP/1.0';
        }

        return $protocol;
    }

    /**
     * Close session if required.
     */
    public function closeSession() {
        $session = $this->getSession();
        if ($session->getData()) {
            if (!$session->isStarted()) {
                $session->start();
            }
            $session->close();
        }
    }

}
