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
namespace ZenMagick\Http\Sacs;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
 * <p>Handler (bean definitions) may be set as a list with the setting <em>zenmagick.http.sacs.handler</em>.</p>
 *
 * <p>To add a handler dynamically the preferred way is to use <code>addHandler()</code> as the default handler list is only evaluated when
 * the manager instance is created.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SacsManager extends ZMObject
{
    protected $container;

    private $mappings;
    private $handlers;
    private $permissionProviders;

    /**
     * Create new instance.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
        $this->reset();
        $context = Runtime::getContext();
        $rootDir = $this->container->getParameter('zenmagick.root_dir');
        $file = sprintf('%s/src/ZenMagick/%sBundle/config/sacs_mappings.yaml', $rootDir, ucfirst($context));
        $this->load($file, false);
        $providers = array();
        if ('admin' == $context) {
            $providers = array('ZenMagick\AdminBundle\Services\DBSacsPermissionProvider');
        }
        $this->loadProviderMappings($providers);
    }

    /**
     * Reset all internal data structures.
     */
    public function reset()
    {
        $this->mappings = array('default' => array(), 'mappings' => array());
        $this->handlers = array();
        $this->permissionProviders = array();
        $context = Runtime::getContext();
        if ('storefront' == $context) {
            $def = 'ZenMagick\StorefrontBundle\Http\Sacs\StorefrontAccountSacsHandler';
        }
        if ('admin' == $context) {
            $def = 'ZenMagick\Http\Sacs\Handler\UserRoleSacsHandler';
        }

        if (empty($def) || !class_exists($def)) return;
        if (null != ($handler = new $def)) {
            $handler->setContainer($this->container);
            $this->handlers[$handler->getName()] = $handler;
        }
    }

    /**
     * Load mappings from a YAML file.
     *
     * @param string filename The yaml filename.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function load($filename, $override=true)
    {
        $mappings = Yaml::parse($filename);
        if ($override) {
            $this->mappings = $mappings;
        } else {
            $this->mappings = Toolbox::arrayMergeRecursive($this->mappings, $mappings);
        }
        foreach (array('default', 'mappings') as $key) {
            if (!array_key_exists($key, $this->mappings)) {
                $this->mappings[$key] = array();
            }
        }
    }

    /**
     * Load mappings from all configured providers.
     *
     * @param array providers List of provider bean definitions.
     */
    public function loadProviderMappings($providers)
    {
        foreach ($providers as $def) {
            if (null != ($provider = Beans::getBean($def)) && $provider instanceof SacsPermissionProvider) {
                $this->permissionProviders[] = $provider;
                foreach ($provider->getMappings() as $providerMapping) {
                    $requestId = $providerMapping['rid'];
                    $type = $providerMapping['type'];
                    $name = $providerMapping['name'];
                    // morph into something we can use
                    if (!array_key_exists($requestId, $this->mappings['mappings'])) {
                        $this->mappings['mappings'][$requestId] = array();
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
                        $this->mappings['mappings'][$requestId] = Toolbox::arrayMergeRecursive($this->mappings['mappings'][$requestId], array($typeKey => array($name)));
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
    public function addHandler($handler)
    {
        $this->hander[] = $handler;
    }

    /**
     * Set a mapping.
     *
     * <p>The <em>authentication</code> value depends on the acutal handler implementation and is passed through <em>as-is</em>.</p>
     *
     * @param string requestId The request id [ie. the request name as set via the <code>rid</code> URL parameter].
     * @param array mapping The mapping.
     */
    public function setMapping($requestId, $mapping)
    {
        if (null == $requestId) {
            throw new RuntimeException("invalid sacs mapping (requestId missing)");
        }
        $this->mappings['mappings'][$requestId] = $mapping;
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
    public function authorize($request, $requestId, $credentials, $action=true)
    {
        $this->container->get('logger')->info('authorizing requestId: '.$requestId);
        // no responsible handler means fail
        $result = null;
        foreach ($this->handlers as $handler) {
            if (null !== ($result = $handler->evaluate($requestId, $credentials, $this))) {
                break;
            }
        }

        $this->container->get('logger')->debug('evaluated by: '.get_class($handler).', result: '.($result ? 'true' : 'false'));
        if (!$result) {
            // null | false
            if (!$action) {
                return false;
            }
            // fire event
            $this->container->get('event_dispatcher')->dispatch('insufficient_credentials', new GenericEvent($this, array('request' => $request, 'credentials' => $credentials)));
            // not required level of authentication
            if (!$request->isXmlHttpRequest()) {
                $request->saveFollowUpUrl();
            }
            $loginRoute = 'login';
            if (Runtime::isContextMatch('admin')) {
                $loginRoute = 'admin_login';
            }
            $request->redirect($this->container->get('router')->generate($loginRoute));
            exit;
        }

        return true;
    }

    /**
     * Get mapping value.
     *
     * @param string requestId The request id.
     * @param string key The mapping key.
     * @param mixed default The mapping key.
     * @return mixed The value or the provided default value; default is <code>null</code>.
     */
    public function getMappingValue($requestId, $key, $default=null)
    {
        if (null == $requestId) {
            $this->container->get('logger')->debug('null is not a valid requestId');

            return null;
        }

        /* evaluate in the following order:
         * a) do we have an explicit mapping,
         * b) do we have a mapping default value
         * c) use provided default
         */

        // init with the given default value
        $value = $default;
        if (array_key_exists($requestId, $this->mappings['mappings']) && is_array($this->mappings['mappings'][$requestId])) {
            // have a mapping for this requestId
            if (array_key_exists($key, $this->mappings['mappings'][$requestId])) {
                $value = $this->mappings['mappings'][$requestId][$key];
            } else {
                // do we have a default mapping?
                if (array_key_exists($key, $this->mappings['default'])) {
                    $value = $this->mappings['default'][$key];
                }
            }
        } else {
            // do we have a default mapping?
            if (array_key_exists($key, $this->mappings['default'])) {
                $value = $this->mappings['default'][$key];
            }
        }

        return $value;
    }

    /**
     * Check if a mapping for the given requestId exists.
     *
     * @param string requestId The request id.
     * @return boolean <code>true</code> if a mapping exists, <code>false</code> if not.
     */
    public function hasMappingForRequestId($requestId)
    {
        return array_key_exists($requestId, $this->mappings['mappings']);
    }

    /**
     * Get all mapped requests.
     *
     * @return array Map with requestId as key and <em>sacs</em> data as value.
     */
    public function getMappings()
    {
        return $this->mappings['mappings'];
    }

    /**
     * Get the default mapping.
     *
     * @return array Default mapping data.
     */
    public function getDefaultMapping()
    {
        return $this->mappings['default'];
    }

}
