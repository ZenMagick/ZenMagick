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
namespace ZenMagick\Http\View;

use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Request;

/**
 * A template view.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TemplateView extends ZMObject implements View
{
    private $resourceResolver;
    private $resourceManager;
    private $variables;
    private $layout;
    private $template;
    private $request;
    private $contentType;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->variables = array();
        $this->layout = null;
        $this->template = null;
        $this->request = null;
        $this->contentType = 'text/html';
    }

    /**
     * Get the content type for this view.
     *
     * @return string The content type.
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set the content type for this view.
     *
     * @param string contentType The content type.
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Set the resource resolver.
     *
     * @param ResourceResolver resourceResolver The resource resolver.
     */
    public function setResourceResolver(ResourceResolver $resourceResolver)
    {
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Get the resource resolver.
     *
     * @return ResourceResolver The resource resolver.
     */
    public function getResourceResolver()
    {
        return $this->resourceResolver;
    }

    /**
     * Get the request.
     *
     * @return ZenMagick\Http\Request The request.
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * Set the resource manager.
     *
     * @param ResourceManager resourceManager The resource manager.
     */
    public function setResourceManager(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
        // associate with this view
        $this->resourceManager->setView($this);
    }

    /**
     * Get the resource manager.
     *
     * @return ResourceResolver The resource manager.
     */
    public function getResourceManager()
    {
        return $this->resourceManager;
    }

    /**
     * Make a variable (value) available under the given name.
     *
     * @param string name The variable name.
     * @param mixed value The value.
     */
    public function setVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Get a variable.
     *
     * @param string name The variable name.
     * @return mixed The value or <code>null</code>
     */
    public function getVariable($name)
    {
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
    public function setVariables($variables)
    {
        $this->variables = array_merge($this->variables, (array) $variables);
    }

    /**
     * Get all available variables in this view.
     *
     * @return array A name/value map.
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set the layout name.
     *
     * @param string layout The layout name.
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get the layout name.
     *
     * @return string The layout name.
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Get the template name.
     *
     * @return string The template name.
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the template name.
     *
     * @param string template The new template name.
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        $filename = $this->getTemplate();

        return $this->getEngine()->exists($filename);
    }

    /**
     * Get the template engine.
     *
     * @return EngineInterface The engine.
     * @todo inject the engine once it won't cause a circular reference issue.
     */
    protected function getEngine()
    {
        return $this->container->get('templating');
    }

    /**
     * Init variables.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    protected function initVariables($request)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function generate($request, $template=null, $variables=array())
    {
        $this->initVariables($request);

        // @todo this (and the previous code) ignores the passed template arg
        // altogether
        $template = $this->getTemplate();

        // render
        $output = $this->fetch($template, $variables);

        // apply resources...
        if (null !== ($resources = $this->resourceManager->getResourceContents())) {
            $output = preg_replace('/<\/head>/', $resources['header'] . '</head>', $output, 1);
            $output = preg_replace('/<\/body>/', $resources['footer'] . '</body>', $output, 1);
        }

        return $output;
    }

    /**
     * Fetch/evaluate the given template.
     *
     * @param string template The template.
     * @param array variables Optional additional template variables; default is an empty array.
     * @return string The template output.
     */
    public function fetch($template, $variables=array())
    {
        $this->initVariables(null);

        // render
        $engine = $this->getEngine();

        return $engine->render($template, array_merge($variables, $this->getVariables()));
    }

}
