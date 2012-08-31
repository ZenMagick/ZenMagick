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
namespace ZenMagick\http\sacs;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Events\Event;
use ZenMagick\Base\Logging\Logging;
use ZenMagick\http\routing\RouteResolver;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Handle access control and security mappings.
 *
 * <p>This manager class provides abstract access to access control methods. The actual processing is delegated to
 * implementations of the <code>SacsHandler</code> interface.
 *
 * <p>Access control mappings define the level of authentication required for resources.
 * Resources in this context are controller or page requests.</p>
 *
 * <p>Controller/resources marked as secure will result in redirects using SSL (if configured), if
 * non secure HTTP is used to access them.</p>
 *
 * <p>Handler (bean definitions) may be set as a list with the setting <em>zenmagick.http.sacs.handler</em>.</p>
 *
 * <p>To add a handler dynamically the preferred way is to use <code>addHandler()</code> as the default handler list is only evaluated when
 * the manager instance is created.</p>
 *
 * <p><strong>NOTE: The only predefined mapping key is <em>secure</em>. It is a boolean flag indicating whether the resource requires
 * a secure access method (ie. SSL/HTTPS) or not.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SacsManager extends ZMObject {
    protected $container;

    private $mappings_;
    private $handlers_;
    private $permissionProviders_;
    private $routeResolver;

    /**
     * Create new instance.
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        parent::__construct();
        $this->reset();
    }

    /**
     * Set an optional RouteResolver
     */
    public function setRouteResolver(RouteResolver $routeResolver) {
        $this->routeResolver = $routeResolver;
    }

    /**
     * Get the RouteResolver
     */
    protected function getRouteResolver() {
        return $this->routeResolver;
    }

    /**
     * Reset all internal data structures.
     */
    public function reset() {
        $this->mappings_ = array('default' => array(), 'mappings' => array());
        $this->handlers_ = array();
        $this->permissionProviders_ = array();
        // @todo use tagged services
        foreach ($this->container->get('settingsService')->get('zenmagick.http.sacs.handler', array('ZenMagick\http\sacs\handler\DefaultSacsHandler')) as $def) {
            if (!class_exists($def)) continue;
            if (null != ($handler = new $def)) {
                $handler->setContainer($this->container);
                $this->handlers_[$handler->getName()] = $handler;
            }
        }
    }

    /**
     * Load mappings from a YAML file.
     *
     * @param string filename The yaml filename.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function load($filename, $override=true) {
        $mappings = Yaml::parse($filename);
        if ($override) {
            $this->mappings_ = $mappings;
        } else {
            $this->mappings_ = Toolbox::arrayMergeRecursive($this->mappings_, $mappings);
        }
        foreach (array('default', 'mappings') as $key) {
            if (!array_key_exists($key, $this->mappings_)) {
                $this->mappings_[$key] = array();
            }
        }
    }

    /**
     * Load mappings from all configured providers.
     *
     * @param array providers List of provider bean definitions.
     */
    public function loadProviderMappings($providers) {
        foreach ($providers as $def) {
            if (null != ($provider = Beans::getBean($def)) && $provider instanceof SacsPermissionProvider) {
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
                        $this->mappings_['mappings'][$requestId] = Toolbox::arrayMergeRecursive($this->mappings_['mappings'][$requestId], array($typeKey => array($name)));
                    }
                }
            }
        }
    }

    /**
     * Add a <code>SacsHandler</code>.
     *
     * @param SacsHandler handler The new handler.
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
     * @param array mapping The mapping.
     */
    public function setMapping($requestId, $mapping) {
        if (null == $requestId) {
            throw new RuntimeException("invalid sacs mapping (requestId missing)");
        }
        $this->mappings_['mappings'][$requestId] = $mapping;
    }

    /**
     * Authorize the current request.
     *
     * <p>If no configured handler is found, all requests will be authorized.</p>
     *
     * @param Request request The current request.
     * @param string requestId The request id to authorize.
     * @param mixed credientials User information; typically a map with username and password.
     * @param boolean action Optional flag to control whether to actually action or not; default is <code>true</code>.
     * @return boolean <code>true</code> if authorization was sucessful.
     */
    public function authorize($request, $requestId, $credentials, $action=true) {
        Runtime::getLogging()->log('authorizing requestId: '.$requestId, Logging::TRACE);
        // no responsible handler means fail
        $result = null;
        foreach ($this->handlers_ as $handler) {
            if (null !== ($result = $handler->evaluate($requestId, $credentials, $this))) {
                break;
            }
        }

        Runtime::getLogging()->log('evaluated by: '.get_class($handler).', result: '.($result ? 'true' : 'false'), Logging::TRACE);
        if (!$result) {
            // null | false
            if (!$action) {
                return false;
            }
            // fire event
            $this->container->get('event_dispatcher')->dispatch('insufficient_credentials', new Event($this, array('request' => $request, 'credentials' => $credentials)));
            // not required level of authentication
            $session = $request->getSession();
            // secure flag: leave to net() to lookup via SacsManager if configured, but leave as default parameter to allow override
            if (!$session->isStarted()) {
                // no valid session
                $request->redirect($request->url($this->container->get('settingsService')->get('zenmagick.http.request.invalidSession')));
                exit;
            }
            if (!$request->isXmlHttpRequest()) {
                $request->saveFollowUpUrl();
            }
            $request->redirect($request->url($this->container->get('settingsService')->get('zenmagick.http.request.login', 'login'), '', true));
            exit;
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
        $requestId = $request->getRequestId();
        $secure = Toolbox::asBoolean($this->getMappingValue($requestId, 'secure', false));
        // check router too
        $routeResolver = $this->getRouteResolver();
        if ((null != $routeResolver) && (null != ($route = $routeResolver->getRouteForId($requestId)))) {
            $requirements = $route->getRequirements();
            $secure |= (array_key_exists('_scheme', $requirements) && 'https' == $requirements['_scheme']);
        }
        $settings = Runtime::getSettings();
        if ($secure && !$request->isSecure() && $settings->get('zenmagick.http.request.secure', true) && $settings->get('zenmagick.http.request.enforceSecure')) {
            Runtime::getLogging()->log('redirecting to enforce secure access: '.$requestId, Logging::TRACE);
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
            Runtime::getLogging()->debug('null is not a valid requestId');
            return null;
        }

        /* evaluate in the following order:
         * a) do we have an explicit mapping,
         * b) do we have a mapping default value
         * c) use provided default
         */

        // init with the given default value
        $value = $default;
        if (array_key_exists($requestId, $this->mappings_['mappings']) && is_array($this->mappings_['mappings'][$requestId])) {
            // have a mapping for this requestId
            if (array_key_exists($key, $this->mappings_['mappings'][$requestId])) {
                $value = $this->mappings_['mappings'][$requestId][$key];
            } else {
                // do we have a default mapping?
                if (array_key_exists($key, $this->mappings_['default'])) {
                    $value = $this->mappings_['default'][$key];
                }
            }
        } else {
            // do we have a default mapping?
            if (array_key_exists($key, $this->mappings_['default'])) {
                $value = $this->mappings_['default'][$key];
            }
        }

        if ('secure' == $key) {
            $value = Toolbox::asBoolean($value);
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
        return $this->getMappingValue($requestId, 'secure', false);
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
