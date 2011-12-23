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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A view.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.view
 */
class View extends ZMObject {
    private $resourceResolver;
    private $variables;
    private $viewId_;
    private $template_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->variables = array();
        $this->viewId_ = null;
        $this->template_ = null;
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
    public function getVarable($name) {
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
        $this->variables = array_merge($this->variables, $variables);
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
     * @return string The contents.
     */
    public function generate($request) {
        // set a few default things...
        $view->setVar('request', $request);
        $view->setVar('session', $request->getSession());
        $toolbox = $request->getToolbox();
        $view->setVar('toolbox', $toolbox);

        // also set individual tools
        $view->setVars($toolbox->getTools());

        // set all plugins
        $settingsService = Runtime::getSettings();
        foreach ($this->container->get('pluginService')->getAllPlugins($settingsService->get('zenmagick.base.context')) as $plugin) {
            $this->setVar($plugin->getId(), $plugin);
        }

        return $this->fetch($request, $this->getTemplate().$settingsService->get('zenmagick.mvc.templates.ext', '.php'));
    }

    /**
     * Get the view id.
     *
     * @return string The view id.
     */
    public function getViewId() {
        return $this->viewId_;
    }

    /**
     * Set the view id.
     *
     * @param string viewId The new view id.
     */
    public function setViewId($viewId) {
        $this->viewId_ = $viewId;
    }

    /**
     * Get the template name.
     *
     * @return string The template name.
     */
    public function getTemplate() {
        return $this->template_;
    }

    /**
     * Set the template name.
     *
     * @param string template The new template name.
     */
    public function setTemplate($template) {
        $this->template_ = $template;
    }

    /**
     * Get the content type for this view.
     *
     * <p>Return the value of the setting <em>zenmagick.mvc.html.contentType</em> or <em>text/html</em> as default.</p>
     *
     * @return string The content type or <code>null</code>.
     */
    public function getContentType() {
        return Runtime::getSettings()->get('zenmagick.mvc.html.contentType', 'text/html');
    }

    /**
     * Get the content encoding.
     *
     * <p>Return the value of the setting <em>zenmagick.mvc.html.charset</em> or <em>UTF-8</em> as default.</p>
     *
     * @return string The content encoding.
     */
    public function getEncoding() {
        return Runtime::getSettings()->get('zenmagick.mvc.html.charset', 'UTF-8');
    }

    /**
     * Fetch/generate the contents of the given template.
     *
     * @param request The current request.
     * @param string template The template name.
     * @param array variables Additional template variables; default is an empty array.
     * @return string The contents.
     */
    public abstract function fetch($request, $template, $variables=array());

    /**
     * Check if the given templates file exists.
     *
     * @param request The current request.
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMVIew::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public abstract function exists($request, $filename, $type=ZMView::TEMPLATE);

    /**
     * Resolve the given (relative) templates filename into a url.
     *
     * @param request The current request.
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMVIew::TEMPLATE</code>.
     * @return string A url.
     */
    public abstract function asUrl($request, $filename, $type=ZMView::TEMPLATE);

    /**
     * Resolve the given templates filename to a fully qualified filename.
     *
     * @param request The current request.
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMVIew::TEMPLATE</code>.
     * @return string A fully qualified filename or <code>null</code>.
     */
    public abstract function path($request, $filename, $type=ZMView::TEMPLATE);

    /**
     * Convert a full file system path to uri.
     *
     * @param request The current request.
     * @param string filename The full filename.
     * @return string The uri or <code>null</code> if the filename is invalid.
     */
    public abstract function file2uri($request, $filename);

    /**
     * Get view utils.
     *
     * @return ZMViewUtils An instance of <code>ZMViewUtils</code> or <code>null</code>.
     */
    public abstract function getViewUtils();

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
     * @param request The current request.
     * @param string path The base path, relative to the template/resource path.
     * @param string regexp Optional filter expression; default is <code>null</code> for none.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMView::RESOURCE</code>.
     * @return array A map of matching filename/relative url pairs.
     */
    public abstract function find($request, $path, $regexp=null, $type=ZMView::RESOURCE);

}
