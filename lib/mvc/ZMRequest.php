<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RequestContext;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\logging\Logging;
use zenmagick\base\events\VetoableEvent;

/**
 * A wrapper around Symfony 2's <code>Symfony\Component\HttpFoundation\Request</code>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMRequest extends HttpFoundationRequest implements ContainerAwareInterface {
    /**
     * Default paramter name containing the request id.
     *
     * <p>Will be used if the 'zenmagick.http.request.idName' is not set.</p>
     */
    const DEFAULT_REQUEST_ID = 'rid';

    private $dispatcher = null;

    /**
     * Populate ParameterBag instances from superglobals
     *
     * @todo don't initialize in the ctor. pass it to the Application in the front controller.
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        $this->initialize($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER, null);
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container=null) {
        $this->container = $container;
    }

    /**
     * Set the dispatcher for this request.
     *
     * @param Dispatcher dispatcher The dispatcher.
     */
    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the dispatcher for this request.
     *
     * @return Dispatcher The dispatcher.
     */
    public function getDispatcher() {
        return $this->dispatcher;
    }

    /**
     * Check if this request is an Ajax request.
     *
     * <p>This default implementation will check for a 'X-Requested-With' header. Subclasses are free to
     * extend and override this method for custom Ajax detecting.</p>
     *
     * @return boolean <code>true</code> if this request is considered an Ajax request.
     */
    public function isXmlHttpRequest() {
        $ajax = $this->getParameter('ajax', null);
        return $ajax != null ? Toolbox::asBoolean($ajax) : parent::isXmlHttpRequest();
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
        if (/*null == $requestId || */null === $params) {
            // if requestId null, keep current and also current params
            $query = $this->query->all();
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
    }

    /**
     * Convert a given relative URL into an absolute one.
     *
     * @param string url The (relative) URL to convert.
     * @param boolean full Set to true to create a full URL incl. the protocol, hostname, port, etc.; default is <code>false</code>.
     * @param boolean secure Set to true to force a secure URL; default is <code>false</code>.
     * @return string The absolute URL.
     */
    public function absoluteUrl($url, $full=false, $secure=false) {
        $url = (!empty($url) && ('/' == $url[0] || false !== strpos($url, '://'))) ? $url : $this->getContext().'/'.$url;
        $secure = Runtime::getSettings()->get('zenmagick.http.request.enforceSecure') && $secure;
        if ($full || ($secure && !$this->isSecure())) {
            // full requested or we need a full URL to ensure it will be secure
            $isSecure = ($this->isSecure() || $secure);
            $scheme = ($this->isSecure() || $secure) ? 'https://' : 'http://';
            $url = $scheme.$this->getHttpHost().$url;
        }

        return $url;
    }

    /**
     * Get the user (if any) for authentication.
     *
     * <p>Creation of the user object is delegated to the configured <code>zenmagick\http\session\UserFactory</code> instance.
     * The factory may be configured as bean defintion via the setting 'zenmagick.http.session.userFactory'.</p>
     *
     * @return mixed A user/credentials object. Default is <code>null</code>.
     */
    public function getAccount() {
        if ($this->container->has('userFactory') && null != ($userFactory = $this->container->get('userFactory'))) {
            return $userFactory->getUser($this);
        }

        return null;
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
     * Get the complete parameter map.
     *
     * GET and POST.
     * @todo change all users ?
     * @param boolean sanitize If <code>true</code>, sanitze value; default is <code>true</code>.
     * @return array Map of all request parameters
     */
    public function getParameterMap($sanitize=true) {
        $map = array();
        $params = array_unique(array_merge($this->request->keys(), $this->query->keys()));
        foreach ($params as $key) {
            // checkbox special case
            if (0 === strpos($key, '_')) {
                $key = substr($key, 1);
            }
            $map[$key] = $this->getParameter($key, null, $sanitize);
        }

        return $map;
    }

    /**
     * Get the name of the request parameter that contains the request id.
     *
     * @return string The request id key.
     */
    public function getRequestIdKey() {
        // called inside c'tor, so no container yet
        return Runtime::getSettings()->get('zenmagick.http.request.idName', self::DEFAULT_REQUEST_ID);
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
        return $this->query->get($this->getRequestIdKey(), 'index');
    }

    /**
     * Set the request id.
     *
     * @param string requestId The new request id.
     */
    public function setRequestId($requestId) {
        $this->query->set($this->getRequestIdKey(), $requestId);
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
        // try GET, then POST
        // @todo we could also just rely on parent::get() as it searches these as well
        foreach (array('query', 'request') as $parameterBag) {
            if ($this->$parameterBag->has($name)) {
                return $sanitize ? self::sanitize($this->$parameterBag->get($name)) : $this->$parameterBag->get($name);
            }
            // special case for checkboxes/radioboxes?
            if ($this->$parameterBag->has('_'.$name)) {
                // checkbox boolean value
                return false;
            }
        }
        return $default;
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
     * @return Toolbox A toolbox instance.
     * @deprecated use container directly
     */
    public function getToolbox() {
        return $this->container->get('toolbox');
    }

    /**
     * Get the URL context for this request.
     *
     * @return string The URL context.
     */
    public function getContext() {
        $context = str_replace('\\', '/', dirname($this->server->get('SCRIPT_NAME')));
        return '/' == $context ? '' : $context;
    }

    /**
     * Get the document root path.
     *
     * @return string The document root.
     */
    public function getDocRoot() {
        $docRoot = $this->server->get('DOCUMENT_ROOT');
        $scriptFileName = $this->server->get('SCRIPT_FILENAME');
        if (empty($docRoot) || 0 !== strpos($scriptFileName, $docRoot)) {
            $phpSelf = $this->server->get('PHP_SELF');
            $docRoot = str_replace(DIRECTORY_SEPARATOR, '/', substr($scriptFileName, 0, 0-strlen($phpSelf)));
        }
        return $docRoot;
    }

    /**
     * Save this request as follow up URL.
     *
     * <p>Typically this happends when a request is received without valid authority.
     * The saved URL will be forwarded to, once permissions is gained (user logged in).</p>
     */
    public function saveFollowUpUrl() {
        $params = $this->query->all();
        $ridKey = $this->getRequestIdKey();
        if (array_key_exists($ridKey, $params)) {
            unset($params[$ridKey]);
        }

        $data = array('requestId' => $this->getRequestId(), 'params' => $params, 'secure' => $this->isSecure());
        $this->getSession()->setValue('followUpUrl', $data, 'zenmagick.http');
    }

    /**
     * Check if a follow up url exists that should be loaded (after a login).
     *
     * @param boolean clear Optional flag to keep or clear the follow up url; default is <code>true</code> to clear.
     * @return string The url to go to or <code>null</code>.
     */
    public function getFollowUpUrl($clear=true) {
        if (null != ($data = $this->getSession()->getValue('followUpUrl', 'zenmagick.http'))) {
            $params = array();
            foreach ($data['params'] as $key => $value) {
                $params[] = $key.'='.$value;
            }
            if ($clear) {
                $this->getSession()->setValue('followUpUrl', null, 'zenmagick.http');
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
        $protocol = $this->server->get('SERVER_PROTOCOL');
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
            if ($session->isStarted()) {
                $session->start();
                $session->close();
            }
        }
    }

    /**
     * Sanitize a given value.
     *
     * @param mixed value A string or array.
     * @return mixed A sanitized version.
     */
    public static function sanitize($value) {
        if (is_string($value)) {
            //$value = preg_replace('/ +/', ' ', $value);
            $value = preg_replace('/[<>]/', '_', $value);
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                $value = stripslashes($value);
            }
            return trim($value);
        } elseif (is_array($value)) {
            while (list($key, $val) = each($value)) {
                $value[$key] = self::sanitize($val);
            }
            return $value;
        }

        return $value;
    }
}
