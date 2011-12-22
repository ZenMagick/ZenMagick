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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;

/**
 * Custom Savant(3).
 *
 * <p>Adds some convenience methods to access templates.</p>
 *
 * <p><strong>ATTENTION:</strong> These methods only make sense if called from
 * within a template.</p>
 *
 * <p>Also, adds support for caching. The config map supports a key <em>cache</em> that
 * is expected to be a class name that implements the following two methods:</p>
 * <dl>
 *   <dt><code>lookup($tpl)</code></dt>
 *   <dd>Query the cache for the given template name and return the cached contents (if any).
 *     If the template is not cached (yet), or is not allowed to be cached, <code>null</code>
 *     should be returned.</dd>
 *   <dt><code>save($tpl, $contents)</code></dt>
 *   <dd>Save the contents of the given template fetch in the cache (if allowed).</dd>
 * </dl>
 *
 * <p>It should be noted that it is the reponsibility of the cache class to decide whether a given
 * template can be cached or not.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.view
 */
class ZMSavant extends Savant3 implements ContainerAwareInterface {
    protected $container;


    /**
     * Create a new instance.
     */
    public function __construct($config=null) {
        parent::__construct($config);
        $this->setConfig($config);
    }


    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container=null) {
        $this->container = $container;
    }

    /**
     * Set the config.
     *
     * @param array config The config map.
     */
    public function setConfig($config) {
        if (null != $config) {
            if (isset($config['cache'])) {
                $this->__config['cache'] = $config['cache'];
            }
            if (isset($this->__config['cache']) && !is_object($this->__config['cache'])) {
                $this->__config['cache'] = Beans::getBean($this->__config['cache']);
            }
            // why isn't that set in Savant3???
            if (isset($config['compiler'])) {
                $this->__config['compiler'] = $config['compiler'];
            }
        }
    }

    /**
     * Check if the given template/resource file exists.
     *
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMView::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($filename, $type=ZMView::TEMPLATE) {
        $file = $this->findFile($type, $filename);
        return !empty($file);
    }

    /**
     * Resolve the given templates filename to a fully qualified filename.
     *
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMView::TEMPLATE</code>.
     * @return string A fully qualified filename or <code>null</code>.
     */
    public function path($filename, $type=ZMView::TEMPLATE) {
        $path = $this->findFile($type, $filename);
        return empty($path) ? null : $path;
    }

    /**
     * Convert a full fs path to uri.
     *
     * @param string filename The full filename.
     * @return string The uri or <code>null</code> if the filename is invalid.
     */
    public function file2uri($filename) {
        $filename = realpath($filename);
        $docRoot = realpath($this->request->getDocRoot());
        if (empty($filename) || empty($docRoot)) {
            return null;
        }
        if (0 !== strpos($filename, $docRoot)) {
            // outside docroot
            return null;
        }

        return str_replace(DIRECTORY_SEPARATOR, '/', substr($filename, strlen($docRoot)));
    }

    /**
     * Resolve the given (relative) templates filename into a url.
     *
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMView::TEMPLATE</code>.
     * @return string A url.
     */
    public function asUrl($filename, $type=ZMView::TEMPLATE) {
        if (null != ($path = $this->findFile($type, $filename))) {
            if (null != ($uri= $this->file2uri($path))) {
                $url = $this->request->absoluteURL($uri);
                Runtime::getLogging()->log('resolve filename '.$filename.' (type='.$type.') as url: '.$url.'; path='.$path, Logging::TRACE);
                return $url;
            }
        }

        Runtime::getLogging()->warn('can\'t resolve filename '.$filename.' (type='.$type.') '.$filename.' to url');
        return '';
    }

    /**
     * {@inheritDoc}
     *
     * Adds a hook for flexible caching.
     */
    public function fetch($tpl=null) {
        // check if caching enabled
        if (isset($this->__config['cache'])) {
            // check for cache hit
            if (null != ($result = call_user_func(array($this->__config['cache'], 'lookup'), $tpl))) {
                return $result;
            }
        }

        // generate content as usual
        $result = parent::fetch($tpl);

        if (isset($this->__config['cache'])) {
            // offer to cache the result
            call_user_func(array($this->__config['cache'], 'save'), $tpl, $result);
        }

        return $result;
    }

    /**
     * Fetch/generate the contents for a given block group id.
     *
     * @param string group The group id.
     * @param array args Optional parameter; default is an empty array.
     * @return string The contents.
     */
    public function fetchBlockGroup($groupId, $args=array()) {
        $contents = '';
        foreach ($this->container->get('blockManager')->getBlocksForId($this->request, $groupId, $args) as $block) {
            Runtime::getLogging()->debug(sprintf('render block, template: %s', $block->getTemplate()));
            $contents .= $block->render($this->request, $this->view);
        }
        return $contents;
    }

    /**
     * Render a widget.
     *
     * @param mixed widget Either a <code>ZMWidget</code> instance or a widget bean definition.
     * @param string name Optional name; default is <code>null</code> for none.
     * @param string value Optional value; default is <code>null</code> for none.
     * @param mixed args Optional parameter; a map of widget properties;  default is <code>null</code>.
     * @return string The widget contents.
     */
    public function widget($widget, $name=null, $value=null, $args=null) {
        $wObj = $widget;
        if (is_string($widget)) {
            $wObj = Beans::getBean($widget);
        }
        if (!($wObj instanceof ZMWidget)) {
            Runtime::getLogging()->debug('invalid widget: '.$widget);
            return '';
        }
        if (null !== $name) {
            $wObj->setName($name);
            if (null === $args || !array_key_exists('id', $args)) {
                // no id set, so default to name
                $wObj->setId($name);
            }
        }
        if (null !== $value) {
            $wObj->setValue($value);
        }
        if (null !== $args) {
            Beans::setAll($wObj, $args);
        }
        return $wObj->render($this->request, $this->view);
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
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMView::RESOURCE</code>.
     * @return array A map of matching filename/relative url pairs.
     */
    public function find($path, $regexp=null, $type=ZMView::RESOURCE) {
        switch ($type) {
        case ZMView::TEMPLATE:
            $dirs = $this->view->getTemplatePath($this->request);
            break;
        case ZMView::RESOURCE:
            $dirs = $this->view->getResourcePath($this->request);
            break;
        default:
            $dirs = array();
            break;
        }

        // iterate in ascending priority, so the more important win
        $files = array();
        foreach ($dirs as $base) {
            $dir = $base.$path;
            if (file_exists($dir) && is_dir($dir)) {
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                if (null != $regexp) {
                    $it = new RegexIterator($it, $regexp, RegexIterator::MATCH);
                }
                for ($it->rewind(); $it->valid(); $it->next()) {
                    $name = str_replace(array($base, '\\'), array('', '/'), $it->current()->getPathname());
                    $files[$name] = $this->asUrl($name);
                }
            }
        }

        return $files;
    }

}
