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
namespace zenmagick\http\templating;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\Logging\Logging;
use zenmagick\http\view\ResourceResolver;
use zenmagick\http\view\View;
use zenmagick\http\widgets\Widget;


/**
 * The ZenMagick PHP template engine.
 *
 * <p><code>render()</code> is the starting point of all template rendering. Parameters passed into <code>render()</code> will
 * be available to all templates called within the render call.</p>
 * <p>Parameters passes into <code>fetch()</code>, in turn, are only visible within the specific template fetched.</p>
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMPhpEngine extends ZMObject implements EngineInterface {
    protected $resourceResolver;
    protected $request;
    protected $properties;


    /**
     * Constructor.
     *
     * @param ResourceResolver resourceResolver A resource resolver.
     * @param LoaderInterface loader A loader instance.
     * @param ZMRequest request The current request.
     */
    public function __construct(ResourceResolver $resourceResolver, \ZMRequest $request) {
        parent::__construct();
        $this->resourceResolver = $resourceResolver;
        $this->request = $request;
        $this->properties = array();
    }


    /**
     * {@inheritDoc}
     */
    public function render($template, array $parameters=array()) {
        // base properties
        $this->properties = array_merge($this->properties, $parameters);
        return $this->fetch($template, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function Xexists($template) {
        return false !== $this->loader->load($filename);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($template) {
        $ext = pathinfo($template, PATHINFO_EXTENSION);
        return 'php' === $ext;
    }

    /**
     * Fetch/evaluate the given template.
     *
     * <p>Alias for render().</p>
     *
     * @param string template The template.
     * @param array variables Optional additional template variables; default is an empty array.
     * @return string The template output.
     */
    public function fetch($template, array $variables=array()) {
        $path = $this->resourceResolver->findResource($template, View::TEMPLATE);
				extract($this->properties, EXTR_REFS | EXTR_SKIP);
				extract($variables, EXTR_REFS | EXTR_SKIP);
        ob_start();
        require $path;
        $result = ob_get_clean();
        return $result;
    }

    /**
     * Check if the given template/resource file exists.
     *
     * @param string file The file, relative to the template path.
     * @param string type The resource type; default is <code>View::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($file, $type=View::TEMPLATE) {
        $path = $this->resourceResolver->findResource($file, $type);
        return !empty($path);
    }

    /**
     * Resolve the given (relative) templates filename into a url.
     *
     * @param string file The file, relative to the template path.
     * @param string type The resource type; default is <code>View::TEMPLATE</code>.
     * @return string A url or empty string.
     */
    public function asUrl($file, $type=View::TEMPLATE) {
        if (null != ($path = $this->resourceResolver->findResource($file, $type))) {
            if (null != ($uri= $this->file2uri($path))) {
                $url = $this->request->absoluteURL($uri);
                Runtime::getLogging()->log(sprintf('resolved file "%s" (type=%s) as url: %s; path=%s', $file, $type, $url, $path), Logging::TRACE);
                return $url;
            }
        }

        Runtime::getLogging()->warn(sprintf('cannot resolve file "%s" (type=%s) to url', $file, $type));
        return '';
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
            //TODO: fix?? $contents .= $block->render($this->request, $this);
            $contents .= $block->render($this->request, $this->properties['view']);
        }
        return $contents;
    }

    /**
     * Render a widget.
     *
     * @param mixed widget Either a <code>Widget</code> instance or a widget bean definition.
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
        if (!($wObj instanceof Widget)) {
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
        return $wObj->render($this->request, $this);
    }

}
