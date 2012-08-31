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
namespace ZenMagick\http\view;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMException;
use ZenMagick\Base\ZMObject;
use ZenMagick\http\plugins\HttpPlugin;

/**
 * Resource resolver.
 *
 * <p>Will resolve the given template or resource with respect for <em>bundles</em>, <em>plugins</em>, <em>context</em> and <em>locale</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ResourceResolver extends ZMObject {
    protected $locations;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->locations = array(View::TEMPLATE => null, View::RESOURCE => null);
    }


    /**
     * Get all valid locations for for the given type.
     *
     * <p>List of locations for the given type in order of decending priority.</p>
     *
     * @param string type The resource type.
     * @return array List of template locations.
     * @see TEMPLATE
     * @see RESOURCE
     */
    public function getLocationsFor($type) {
        switch ($type) {
        case View::TEMPLATE:
            return $this->getTemplateLocations();
        case View::RESOURCE:
            return $this->getResourceLocations();
        }
        throw new ZMException(sprintf('invalid resource type: "%s"', $type));
    }

    /**
     * Get all valid locations for templates.
     *
     * <p>List of locations for templates in order of decending priority.</p>
     *
     * @return array List of template locations.
     */
    public function getTemplateLocations() {
        if (null === $this->locations[View::TEMPLATE]) {
            $locations = array();

            // available locale
            $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

            // add bundles as fallback fallback fallback
            foreach ($this->container->getParameterBag()->get('kernel.bundles') as $bundleName => $bundleClass) {
                $rclass = new ReflectionClass($bundleClass);
                $bundlePath = dirname($rclass->getFilename());
                $locations[] = $bundlePath.'/Resources';
            }

            // add plugins as fallback fallback
            foreach ($this->container->get('pluginService')->getPluginsForContext() as $plugin) {
                if ($plugin instanceof HttpPlugin) {
                    $locations[] = $plugin->getTemplatePath();
                    foreach ($localeCodes as $code) {
                        $locations[] = sprintf('%s/locale/%s', $plugin->getTemplatePath(), $code);
                    }
                }
            }

            $locations = array_merge($locations, $this->getApplicationTemplateLocations());
            $this->locations[View::TEMPLATE] = array_reverse($this->validateLocations($locations));
        }

        return $this->locations[View::TEMPLATE];
    }

    /**
     * Get a list of application template locations.
     *
     * @return array List of template locations.
     */
    protected function getApplicationTemplateLocations() {
        return array($this->getApplicationTemplatePath());
    }

    /**
     * Get all valid locations for resources.
     *
     * <p>List of locations for resources in order of decending priority.</p>
     *
     * @return array List of resource locations.
     */
    public function getResourceLocations() {
        if (null === $this->locations[View::RESOURCE]) {
            $locations = array();

            // available locale
            $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

            // add bundles as fallback fallback fallback
            foreach ($this->container->getParameterBag()->get('kernel.bundles') as $bundleName => $bundleClass) {
                $rclass = new ReflectionClass($bundleClass);
                $bundlePath = dirname($rclass->getFilename());
                $locations[] = $bundlePath.'/Resources/public';
            }

            // add plugins as fallback fallback
            foreach ($this->container->get('pluginService')->getPluginsForContext() as $plugin) {
                if ($plugin instanceof HttpPlugin) {
                    $locations[] = $plugin->getResourcePath();
                    foreach ($localeCodes as $code) {
                        $locations[] = sprintf('%s/locale/%s', $plugin->getResourcePath(), $code);
                    }
                }
            }

            $docroot = $this->getApplicationDocRoot();
            $locations[] = $docroot;


            // add path for locale specific resources
            foreach ($localeCodes as $code) {
                $locations[] = $docroot.'/locale/'.$code;
            }

            $locations = array_merge($locations, $this->getApplicationResourceLocations());
            $this->locations[View::RESOURCE] = array_reverse($this->validateLocations($locations));
        }

        return $this->locations[View::RESOURCE];
    }

    /**
     * Get a list of application resource locations.
     *
     * @return array List of resource locations.
     */
    protected function getApplicationResourceLocations() {
        return array($this->getApplicationDocRoot());
    }

    /**

    /**
     * Get the application template path.
     *
     * @return string The path to the application templates.
     */
    public function getApplicationTemplatePath() {
        return Runtime::getApplicationPath().'/templates/';
    }

    /**
     * Get the application doc root.
     *
     * @return string The path to the application doc root.
     */
    public function getApplicationDocRoot() {
        return Runtime::getApplicationPath().'/web/';
    }

    /**
     * Validate a list of locations.
     *
     * @param array List of locations.
     * @return array List of valid locations.
     */
    protected function validateLocations($locations) {
        $valid = array();
        foreach ($locations as $location) {
            $location = realpath($location);
            if (!empty($location) && file_exists($location)) {
                $valid[] = $location;
            }
        }
        return $valid;
    }

    /**
     * Determine the type by either picking a prefixed resource type or the given default.
     *
     * @param string resource The resource.
     * @param string type The default.
     * @return array ($resource, $type) The final resource name and type.
     */
    public function resolveType($resource, $type) {
        if (0 === strpos($resource, VIEW::TEMPLATE)) {
            return array(substr($resource, 9), VIEW::TEMPLATE);
        } else if (0 === strpos($resource, VIEW::RESOURCE)) {
            return array(substr($resource, 9), VIEW::RESOURCE);
        }
        return array($resource, $type);
    }

    /**
     * Find templates/resources for the given path.
     *
     * <p><strong>Example:</strong></p>
     *
     * <p>Find all styles in a particular folder (<em>style</em>).</p>
     * <code><pre>
     *   $styles = $this->find('style', '/css/');
     *   foreach ($styles as $name => $url) {
     *    echo '<link rel="stylesheet" type="text/css" href="'.$url.'"/>';
     *   }
     * </pre></code>
     *
     * <p>Alternatively, using the build in $resource manager, it would look like this:</p>
     * <code><pre>
     *   $styles = $this->find('style', '/css/');
     *   foreach ($styles as $name => $url) {
     *    $resourceManager->cssFile($name);
     *   }
     * </pre></code>
     *
     * @param string path The base path, relative to the template/resource path.
     * @param string regexp Optional filter expression; default is <code>null</code> for none.
     * @param string type The resource type; default is <code>View::RESOURCE</code>.
     * @return array A map of matching filenames.
     */
    public function find($path, $regexp=null, $type=View::RESOURCE) {
        list($path, $type) = $this->resolveType($path, $type);
        $locations = $this->getLocationsFor($type);

        // iterate in ascending priority, so the more important come first
        $files = array();
        foreach ($locations as $location) {
            $base = $location.'/'.$path;
            if (file_exists($base) && is_dir($base)) {
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
                if (null != $regexp) {
                    $it = new RegexIterator($it, $regexp, RegexIterator::MATCH);
                }
                for ($it->rewind(); $it->valid(); $it->next()) {
                    $name = str_replace(array($base, '\\'), array('', '/'), $it->current()->getPathname());
                    $files[] = $name;
                }
            }
        }

        return $files;
    }

    /**
     * Check if the given resource exists.
     *
     * @param string resource A relative path to the resource.
     * @param string type The resource type; default is <code>View::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($resource, $type=View::TEMPLATE) {
        list($resource, $type) = $this->resolveType($resource, $type);
        $file = $this->findResource($resource, $type);
        return !empty($file);
    }

    /**
     * Find a given resource.
     *
     * @param string resource A relative path to the resource.
     * @param string type The resource type; default is <code>View::RESOURCE</code>.
     * @return string The path to the resource or <code>null</code>.
     */
    public function findResource($resource, $type=View::RESOURCE) {
        list($resource, $type) = $this->resolveType($resource, $type);
        $locations = $this->getLocationsFor($type);
        foreach ($locations as $location) {
            $path = $location.'/'.$resource;

            // is the path based on a stream?
            if (false === strpos($path, '://')) {
                $path = realpath($path);
            }

            if (!empty($path) && file_exists($path) && is_readable($path) && substr($path, 0, strlen($location)) == $location) {
                return $path;
            }
        }
        return false;
    }

}
