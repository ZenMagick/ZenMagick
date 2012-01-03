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
namespace zenmagick\http\view;

use ReflectionClass;
use RegexIterator;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
use zenmagick\base\ZMObject;

/**
 * Resource resolver.
 *
 * <p>Will resolve the given template or resource with respect for <em>bundles</em>, <em>plugins</em>, <em>context</em> and <em>locale</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.view
 */
class ResourceResolver extends ZMObject {
    const TEMPLATE = 'template';
    const RESOURCE = 'resource';
    protected $locations;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->locations = array(self::TEMPLATE => null, self::RESOURCE => null);
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
        case self::TEMPLATE:
            return $this->getTemplateLocations();
        case self::RESOURCE:
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
        if (null === $this->locations[self::TEMPLATE]) {
            $path = array();

            // available locale
            $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

            // add bundles as fallback fallback fallback
            foreach ($this->container->getParameterBag()->get('kernel.bundles') as $bundleName => $bundleClass) {
                $rclass = new ReflectionClass($bundleClass);
                $bundlePath = dirname($rclass->getFilename());
                $path[] = $bundlePath.'/Resources';
            }

            // add plugins as fallback fallback
            foreach ($this->container->get('pluginService')->getAllPlugins(Runtime::getSettings()->get('zenmagick.base.context')) as $plugin) {
                $ppath = $plugin->getPluginDirectory().'content/';
                $path[] = $ppath;
                foreach ($localeCodes as $code) {
                    $path[] = $ppath.'/locale/'.$code;
                }
            }

            $path = array_merge($path, $this->getApplicationTemplateLocations());
            $this->locations[self::TEMPLATE] = array_reverse($this->validateLocations($path));
        }

        return $this->locations[self::TEMPLATE];
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
        if (null === $this->locations[self::RESOURCE]) {
            $path = array();

            // available locale
            $localeCodes = array_reverse($this->container->get('localeService')->getValidLocaleCodes());

            // add plugins as well
            foreach ($this->container->get('pluginService')->getAllPlugins(Runtime::getSettings()->get('zenmagick.base.context')) as $plugin) {
                $ppath = $plugin->getPluginDirectory().'/content/';
                $path[] = $ppath;
                foreach ($localeCodes as $code) {
                    $path[] = $ppath.'/locale/'.$code;
                }
            }

            $docroot = $this->getApplicationDocRoot();
            $path[] = $docroot;


            // add path for locale specific resources
            foreach ($localeCodes as $code) {
                $path[] = $docroot.'/locale/'.$code;
            }

            $path = array_merge($path, $this->getApplicationResourceLocations());
            $this->locations[self::RESOURCE] = array_reverse($this->validateLocations($path));
        }

        return $this->locations[self::RESOURCE];
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
        return Runtime::getApplicationPath().'templates/';
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
     * <p>Alternatively, using the build in $resource helper, it would look like this:</p>
     * <code><pre>
     *   $styles = $this->find('style', '/css/');
     *   foreach ($styles as $name => $url) {
     *    $resource->cssFile($name);
     *   }
     * </pre></code>
     *
     * @param string path The base path, relative to the template/resource path.
     * @param string regexp Optional filter expression; default is <code>null</code> for none.
     * @param string type The resource type; default is <code>ResourceResolver::RESOURCE</code>.
     * @return array A map of matching filenames.
     */
    public function find($path, $regexp=null, $type=ResourceResolver::RESOURCE) {
        $locations = $this->getLocationsFor($type);

        // iterate in ascending priority, so the more important come first
        $files = array();
        foreach ($locations as $location) {
            $base = $location.$path;
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
     * @param string type The resource type; default is <code>ResourceResolver::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($resource, $type=ResourceResolver::TEMPLATE) {
        $file = $this->findResource($resource, $type);
        return !empty($file);
    }

    /**
     * Find a given resource.
     *
     * @param string resource A relative path to the resource.
     * @param string type The resource type.
     * @return string The path to the resource or <code>null</code>.
     */
    public function findResource($resource, $type) {
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
