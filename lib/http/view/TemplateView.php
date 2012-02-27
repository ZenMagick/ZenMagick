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

use Exception;
use Symfony\Component\Templating\DelegatingEngine;
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
 * A template view.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TemplateView extends ZMObject implements View {
    private $resourceResolver;
    private $resourceManager;
    private $variables;
    private $layout;
    private $template;
    private $request;
    private $contentType;
    private $encoding;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->variables = array();
        $this->layout = null;
        $this->template = null;
        $this->request = null;
        $this->contentType = 'text/html';
        $this->encoding = Runtime::getSettings()->get('zenmagick.http.html.charset', 'utf-8');
    }


    /**
     * Get the content type for this view.
     *
     * @return string The content type.
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * Set the content type for this view.
     *
     * @param string contentType The content type.
     */
    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }

    /**
     * Get the encoding for this view.
     *
     * @return string The encoding.
     */
    public function getEncoding() {
        return $this->encoding;
    }

    /**
     * Set the encoding for this view.
     *
     * @param string encoding The encoding.
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
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
        return $this->template;
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
     * {@inheritDoc}
     */
    public function isValid() {
        $filename = $this->getTemplate();
        return $this->resourceResolver->exists($filename);
    }

    /**
     * Get the template engine.
     *
     * @return EngineInterface The engine.
     */
    protected function getEngine() {
        $engine = new DelegatingEngine();
        foreach ($this->container->findTaggedServiceIds('zenmagick.http.templates.engine') as $id => $args) {
            $engine->addEngine($this->container->get($id));
        }
        return $engine;
    }

    /**
     * Init variables.
     *
     * @param ZMRequest request The current request.
     */
    protected function initVariables($request) {
        if (array_key_exists('view', $this->variables)) {
            return;
        }

        $settingsService = Runtime::getSettings();

        // set some standard things
        $this->setVariable('container', $this->container);
        $this->setVariable('resources', $this->getResourceManager());
        $this->setVariable('resourceManager', $this->getResourceManager());
        $this->setVariable('resourceResolver', $this->getResourceResolver());
        $this->setVariable('view', $this);
        if (null == $request) {
            $request = $this->container->get('request');
        }
        $this->setVariable('request', $request);
        $this->setVariable('session', $request->getSession());
        $this->setVariable('settingsService', $settingsService);

        // find services tagged as view variables
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

    }

    /**
     * {@inheritDoc}
     */
    public function generate($request, $template=null, $variables=array()) {
        $this->initVariables($request);

        // sort out the actual template and, if a layout is used, the viewTemplate
        $template = null;
        $layout = $this->getLayout();
        try {
            if (!empty($layout)) {
                $template = $layout;
                $viewTemplate = $this->getTemplate();
                $this->setVariable('viewTemplate', $viewTemplate);
            } else {
                $template = $this->getTemplate();
            }

            // render
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
     * Fetch/evaluate the given template.
     *
     * @param string template The template.
     * @param array variables Optional additional template variables; default is an empty array.
     * @return string The template output.
     */
    public function fetch($template, $variables=array()) {
        $this->initVariables(null);

        // render
        $engine = $this->getEngine();
        return $engine->render($template, array_merge($variables, $this->getVariables()));
    }

    /**
     * Resolve the given (relative) templates filename into a url.
     *
     * @param string file The file, relative to the template path.
     * @return string A url or empty string.
     * @todo: move elsewhere
     */
    public function asUrl($file) {
        if (null != ($path = $this->resourceResolver->findResource($file, View::TEMPLATE))) {
            if (null != ($uri= $this->file2uri($path))) {
                $url = $this->request->absoluteURL($uri);
                Runtime::getLogging()->log(sprintf('resolved file "%s" as url: %s; path=%s', $file, $url, $path), Logging::TRACE);
                return $url;
            }
        }

        Runtime::getLogging()->warn(sprintf('cannot resolve file "%s" to url', $file));
        return '';
    }

    /**
     * Convert a full fs path to uri.
     *
     * @param string filename The full filename.
     * @return string The uri or <code>null</code> if the filename is invalid.
     * @todo: move elsewhere together with asUrl
     */
    protected function file2uri($filename) {
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

}
