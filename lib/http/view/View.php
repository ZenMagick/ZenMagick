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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
use zenmagick\base\ZMObject;
use zenmagick\base\logging\Logging;
use zenmagick\base\events\Event;
use zenmagick\http\widgets\Widget;
use zenmagick\http\toolbox\Toolbox;
use zenmagick\http\toolbox\ToolboxTool;

/**
 * A view.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class View extends ZMObject {
    const TEMPLATE = 'template';
    const RESOURCE = 'resource';
    private $resourceResolver;
    private $resourceManager;
    private $variables;
    private $layout;
    private $template;
    private $request;
    private $filters;
    private $cache;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->variables = array();
        $this->layout = null;
        $this->template = null;
        $this->request = null;
        $this->filters = array();
        $this->cache = null;
    }


    /**
     * Set the resource resolver.
     *
     * @param ResourceResolver resourceResolver The resource resolver.
     */
    public function setResourceResolver(ResourceResolver $resourceResolver) {
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Get the resource resolver.
     *
     * @return ResourceResolver The resource resolver.
     */
    public function getResourceResolver() {
        return $this->resourceResolver;
    }

    /**
     * Add a filter.
     *
     * @param ViewFilter filter The filter.
     */
    public function addFilter(ViewFilter $filter) {
        $this->filters[] = $filter;
    }

    /**
     * Set the cache.
     *
     * @param ViewCache cache The cache.
     */
    public function setCache(ViewCache $cache) {
        $this->cache = $cache;
    }

    /**
     * Get the cache.
     *
     * @return ViewCache The cache.
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Set the request.
     *
     * @param ZMRequest request The request.
     */
    public function setRequest(\ZMRequest $request) {
        $this->request = $request;
    }

    /**
     * Get the request.
     *
     * @return ZMRequest The request.
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Set the resource manager.
     *
     * @param ResourceManager resourceManager The resource manager.
     */
    public function setResourceManager(ResourceManager $resourceManager) {
        $this->resourceManager = $resourceManager;
        // associate with this view
        $this->resourceManager->setView($this);
    }

    /**
     * Get the resource manager.
     *
     * @return ResourceResolver The resource manager.
     */
    public function getResourceManager() {
        return $this->resourceManager;
    }

    /**
     * Make a variable (value) available under the given name.
     *
     * @param string name The variable name.
     * @param mixed value The value.
     */
    public function setVariable($name, $value) {
        $this->variables[$name] = $value;
    }
    // TODO: backw comp: remove
    public function getVar($name) { if ('resources' == $name) { return $this->getResourceManager(); } $this->getVariable($name); }
    public function setVar($name, $value) { $this->setVariable($name, $value); }
    public function setVars($values) { $this->setVariables($values); }
    public function assign($values) { $this->setVariables($values); }

    /**
     * Get a variable.
     *
     * @param string name The variable name.
     * @return mixed The value or <code>null</code>
     */
    public function getVariable($name) {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }

        return null;
    }

    /**
     * Set multiple variables.
     *
     * @param array variables A map of name/value pairs.
     */
    public function setVariables($variables) {
        $this->variables = array_merge($this->variables, (array)$variables);
    }

    /**
     * Get all available variables in this view.
     *
     * @return array A name/value map.
     */
    public function getVariables() {
        return $this->variables;
    }

    /**
     * Set the layout name.
     *
     * @param string layout The layout name.
     */
    public function setLayout($layout) {
        $this->layout = $layout;
    }

    /**
     * Get the layout name.
     *
     * @return string The layout name.
     */
    public function getLayout() {
        return $this->layout;
    }

    /**
     * Get the template name.
     *
     * @return string The template name.
     */
    public function getTemplate() {
        // todo: backwards comp. remove
        return 'views/'.$this->template;
    }

    /**
     * Set the template name.
     *
     * @param string template The new template name.
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the view is valid.
     */
    public function isValid() {
        $filename = $this->getTemplate().Runtime::getSettings()->get('zenmagick.http.templates.ext', '.php');
        return $this->resourceResolver->exists($filename);
    }

    /**
     * Shortcut to generate the contents for the currenty set template.
     *
     * <p>The template extension is taken from the <em>'zenmagick.mvc.templates.ext'</em setting.</p>
     *
     * @param ZMRequest request The current request.
     * @param string template Optional template override; default is <code>null</code>.
     * @param array variables Optional additional template variables; default is an empty array.
     * @return string The contents.
     */
    public function generate($request, $template=null, $variables=array()) {
        $settingsService = Runtime::getSettings();

        // set some standard things
        $this->setVariable('container', $this->container);
        $this->setVariable('resources', $this->getResourceManager());
        $this->setVariable('resourceManager', $this->getResourceManager());
        $this->setVariable('view', $this);
        $this->setVariable('request', $request);
        $this->setVariable('session', $request->getSession());
        $this->setVariable('settingsService', $settingsService);

        foreach ($this->container->findTaggedServiceIds('zenmagick.http.view.variable') as $id => $args) {
            $key = null;
            foreach ($args as $elem) {
                foreach ($elem as $key => $value) {
                    if ('key' == $key && $value) {
                        $key = $value;
                        break;
                    }
                }
            }
            $obj = $this->container->get($id);
            if ($obj instanceof Toolbox) {
                foreach ($obj->getTools() as $name => $tool) {
                    if ($tool instanceof ToolboxTool) {
                        $tool->setView($this);
                    }
                    $this->setVariable($name, $tool);
                }
            }
            $this->setVariable($key, $obj);
        }

        // set all plugins
        foreach ($this->container->get('pluginService')->getAllPlugins($settingsService->get('zenmagick.base.context')) as $plugin) {
            $this->setVariable($plugin->getId(), $plugin);
        }

        // sort out the actual template and, if a layout is used, the viewTemplate
        $template = null;
        $layout = $this->getLayout();
        try {
            if (!empty($layout)) {
                $template = $layout;
                $viewTemplate = $this->getTemplate().$settingsService->get('zenmagick.http.templates.ext', '.php');
                $this->setVariable('viewTemplate', $viewTemplate);
            } else {
                $template = $this->getTemplate();
            }

            $template .= $settingsService->get('zenmagick.http.templates.ext', '.php');
            $output = $this->fetch($template, $variables);
            if (null !== ($resources = $this->resourceManager->getResourceContents())) {
                // apply resources...
                $output = preg_replace('/<\/head>/', $resources['header'] . '</head>', $output, 1);
                $output = preg_replace('/<\/body>/', $resources['footer'] . '</body>', $output, 1);
            }

            return $output;
        } catch (Exception $e) {
            Runtime::getLogging()->dump($e, 'failed to fetch template: '.$template, Logging::ERROR);
            throw new ZMException('failed to fetch template: '.$template, 0, $e);
        }
    }

    /**
     * Compile the given file.
     *
     * <p>Default implementation that just returns the given file name.</p>
     *
     * @param string file The file to compile.
     * @return string Path to the compiled file.
     */
    public function compile($file) {
        return $file;
    }

    /**
     * Apply configured filters to the given string.
     *
     * @param string s The string.
     * @return string The filtered result.
     * @todo: implement
     */
    public function applyFilters($s) {
        foreach ($this->filters as $filter) {
            $s = $filter->apply($s);
        }
        return $s;
    }

    /**
     * Fetch/evaluate the given template.
     *
     * @param string template The template.
     * @param array variables Optional additional template variables; default is an empty array.
     * @return string The template output.
     */
    public function fetch($template, $variables=array()) {
        // todo: backwards comp. remove
        if ($template instanceof \ZMRequest) {
            // old style
            $args = func_get_args();
            array_shift($args);
            $template = $args[0];
            if (1 < count($args)) {
                $variables = $args[1];
            } else {
                $variables = array();
            }
        }

        // resolve template
        if (null == ($file = $this->resourceResolver->findResource($template, View::TEMPLATE))) {
            throw new ZMException(sprintf('template not found: %s', $template));
        }

        // check if caching enabled
        if (null != $this->cache && $this->cache->eligible($template)) {
            // check for cache hit
            if (null != ($result = $this->cache->lookup($template))) {
                return $result;
            }
        }

        $file = $this->compile($file);

        // prepare env
				extract($this->getVariables(), EXTR_REFS);
        // these are transient
				extract($variables, EXTR_REFS);

        if (ZM_ENVIRONMENT == 'dev') {
            $event = new Event($this, array('file' => $file));
            Runtime::getEventDispatcher()->dispatch('fetch_template', new Event($this, array('request' => $this->request, 'view' => $this, 'file' => $file)));
        }

        ob_start();
        require $file;
        $result = $this->applyFilters(ob_get_clean());

        // if we have a cache, keep it
        if (null != $this->cache && $this->cache->eligible($template)) {
            $this->cache->save($template, $result);
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
            $contents .= $block->render($this->request, $this);
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
     * Check if the given template/resource file exists.
     *
     * @param string file The file, relative to the template path.
     * @param string type The resource type; default is <code>View::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($file, $type=View::TEMPLATE) {
        // todo: backwards comp. remove
        if ($file instanceof \ZMRequest) {
            // old style
            $args = func_get_args();
            array_shift($args);
            $file = $args[0];
            if (1 < count($args)) {
                $type = $args[1];
            } else {
                $type = View::TEMPLATE;
            }
        }
        $path = $this->resourceResolver->findResource($file, $type);
        return !empty($path);
    }

}
