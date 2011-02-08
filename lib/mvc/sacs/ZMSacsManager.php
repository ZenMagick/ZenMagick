<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Handle access control and security mappings.
 *
 * <p>This manager class provides abstract access to access control methods. The actual processing is delegated to
 * implementations of the <code>ZMSacsHandler</code> interface.
 *
 * <p>Access control mappings define the level of authentication required for resources.
 * Resources in this context are controller or page requests.</p>
 *
 * <p>Controller/resources marked as secure will result in redirects using SSL (if configured), if
 * non secure HTTP is used to access them.</p>
 *
 * <p>Default handler (class names) may be set as a comma separated list with the setting <em>zenmagick.mvc.sacs.handler</em>.</p>
 *
 * <p>To add handler dynamically the preferred way is to use <code>addHandler()</code> as the default handler list is only evaluated when
 * the manager instance is created.</p>
 *
 * <p><strong>NOTE: The only required element for each mapping (if done via YAML) is <em>'level'</em>. It is expected to be boolean and
 * indicates whether the configured resource requires secvu
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.sacs
 */
class ZMSacsManager extends ZMObject {
    private $mappings_;
    private $handler_;
    private $permissionProviders_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->reset();
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
    public static function instance() {
        return ZMRuntime::singleton('SacsManager');
    }


    /**
     * Reset all internal data structures.
     */
    public function reset() {
        $this->mappings_ = array('default' => array(), 'mappings' => array());
        $this->handler_ = array();
        $this->permissionProviders_ = array();
        foreach (explode(',', ZMSettings::get('zenmagick.mvc.sacs.handler')) as $class) {
            if (null != ($handler = ZMBeanUtils::getBean($class))) {
                $this->handler_[$handler->getName()] = $handler;
            }
        }
    }

    /**
     * Load mappings from a YAML style string.
     *
     * @param string yaml The yaml style mappings.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function load($yaml, $override=true) {
        $this->mappings_ = ZMRuntime::yamlParse($yaml, $this->mappings_, $override);
    }

    /**
     * Load mappings from all configured providers.
     *
     * @param string providers Comma separated list of provider bean definitions.
     */
    public function loadProviderMappings($providers) {
        foreach (explode(',', $providers) as $class) {
            if (null != ($provider = ZMBeanUtils::getBean($class)) && $provider instanceof ZMSacsPermissionProvider) {
                $this->permissionProviders_[] = $provider;
                foreach ($provider->getMappings() as $providerMapping) {
                    $requestId = $providerMapping['rid'];
                    $type = $providerMapping['type'];
                    $name = $providerMapping['name'];
                    // morph into something we can use
                    if (!array_key_exists($requestId, $this->mappings_['mappings'])) {
                        $this->mappings_['mappings'][$requestId] = array();
                    }
                    $typeKey = null;
                    switch ($type) {
                    case 'role':
                        $typeKey = 'roles';
                        break;
                    case 'user':
                        $typeKey = 'users';
                        break;
                    }
                    if (null != $typeKey) {
                        $this->mappings_['mappings'][$requestId] = ZMLangUtils::arrayMergeRecursive($this->mappings_['mappings'][$requestId], array($typeKey => array($name)));
                    }
                }
            }
        }
    }

    /**
     * Add a <code>ZMSacsHandler</code>.
     *
     * @param ZMSacsHandler handler The new handler.
     */
    public function addHandler($handler) {
        $this->hander_[] = $handler;
    }

    /**
     * Set a mapping.
     *
     * <p>The <em>authentication</code> value depends on the acutal handler implementation and is passed through <em>as-is</em>.</p>
     *
     * @param string requestId The request id [ie. the request name as set via the <code>rid</code> URL parameter].
     * @param mixed authentication The level of authentication required; default is <code>null</code>.
     * @param boolean secure Mark resource as secure; default is <code>true</code>.
     * @param array args Optional additional parameter map; default is an empty array.
     */
    public function setMapping($requestId, $authentication=null, $secure=true, $args=array()) {
        if (null == $requestId) {
            throw new ZMException("invalid sacs mapping (requestId missing)");
        }
        $this->mappings_['mappings'][$requestId] = ZMLangUtils::arrayMergeRecursive($args, array('level' => $authentication, 'secure' => $secure));
    }

    /**
     * Authorize the current request.
     *
     * <p>If no configured handler is found, all requests will be authorized.</p>
     *
     * @param ZMRequest request The current request.
     * @param string requestId The request id to authorize.
     * @param mixed credientials User information; typically a map with username and password.
     * @param boolean action Optional flag to control whether to actually action or not; default is <code>true</code>.
     * @return boolean <code>true</code> if authorization was sucessful.
     */
    public function authorize($request, $requestId, $credentials, $action=true) {
        ZMLogging::instance()->log('authorizing requestId: '.$requestId, ZMLogging::TRACE);
        foreach ($this->handler_ as $handler) {
            if (null !== ($result = $handler->evaluate($requestId, $credentials, $this))) {
                ZMLogging::instance()->log('evaluated by: '.get_class($handler).', result: '.($result ? 'true' : 'false'), ZMLogging::TRACE);
                if (false === $result) {
                    if (!$action) {
                        return false;
                    }
                    // fire event
                    Runtime::getEventDispatcher()->notify(new Event($this, 'insufficient_credentials', array('request' => $request, 'credentials' => $credentials)));
                    // not required level of authentication
                    $session = $request->getSession();
                    // secure flag: leave to net() to lookup via ZMSacsManager if configured, but leave as default parameter to allow override
                    if (!$session->isStarted()) {
                        // no valid session
                        $request->redirect($request->url(ZMSettings::get('zenmagick.mvc.request.invalidSession')));
                        exit;
                    }
                    $request->saveFollowUpUrl();
                    $request->redirect($request->url(ZMSettings::get('zenmagick.mvc.request.login', 'login'), '', true));
                    exit;
                }
                break;
            }
        }

        return true;
    }

    /**
     * Ensure the page is accessed using proper security.
     *
     * <p>If a page is requested using HTTP and the page is mapped as <em>secure</em>, a
     * redirect using SSL will be performed.</p>
     *
     * @param string requestId The request id.
     */
    public function ensureAccessMethod($request) {
        $secure = ZMLangUtils::asBoolean($this->getMappingValue($request->getRequestId(), 'secure', false));
        if ($secure && !$request->isSecure() && ZMSettings::get('zenmagick.mvc.request.secure') && ZMSettings::get('zenmagick.mvc.request.enforceSecure')) {
            ZMLogging::instance()->log('redirecting to enforce secure access: '.$request->getRequestId(), ZMLogging::TRACE);
            $request->redirect($request->url(null, null, true));
        }
    }

    /**
     * Get mapping value.
     *
     * @param string requestId The request id.
     * @param string key The mapping key.
     * @param mixed default The mapping key.
     * @return mixed The value or the provided default value; default is <code>null</code>.
     */
    public function getMappingValue($requestId, $key, $default=null) {
        if (null == $requestId) {
            ZMLogging::instance()->log('null is not a valid requestId', ZMLogging::DEBUG);
            return null;
        }

        /* evaluate in the following order:
         * a) do we have an explicit mapping,
         * b) do we have a mapping default value
         * c) use provided default
         */
        $value = $default;
        if (array_key_exists($requestId, $this->mappings_['mappings'])) {
            // have explicit mapping for this requestId
            if (array_key_exists($key, $this->mappings_['mappings'][$requestId])) {
                $value = $this->mappings_['mappings'][$requestId][$key];
            }
        } else {
            // do we have a default mapping?
            if (array_key_exists($key, $this->mappings_['default'])) {
                $value = $this->mappings_['default'][$key];
            }
        }


        if ('secure' == $key) {
            $value = ZMLangUtils::asBoolean($value);
        }

        return $value;
    }

    /**
     * Check if a request to the given page [name] is required to be secure.
     *
     * @param string requestId The request id.
     * @return boolean <code>true</code> if a secure conenction is required.
     */
    public function requiresSecurity($requestId) {
        return $this->getMappingValue($requestId, 'level', false);
    }

    /**
     * Check if a mapping for the given requestId exists.
     *
     * @param string requestId The request id.
     * @return boolean <code>true</code> if a mapping exists, <code>false</code> if not.
     */
    public function hasMappingForRequestId($requestId) {
        return array_key_exists($requestId, $this->mappings_['mappings']);
    }

    /**
     * Get all mapped requests.
     *
     * @return array Map with requestId as key and <em>sacs</em> data as value.
     */
    public function getMappings() {
        return $this->mappings_['mappings'];
    }

    /**
     * Get the default mapping.
     *
     * @return array Default mapping data.
     */
    public function getDefaultMapping() {
        return $this->mappings_['default'];
    }

}
