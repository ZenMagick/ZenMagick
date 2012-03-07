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
namespace zenmagick\http\templating;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
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
    protected $view;
    protected $request;
    protected $templateCache;
    protected $properties;


    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->request = null;
        $this->view = null;
        $this->templateCache = null;
        $this->properties = array();
    }


    /**
     * {@inheritDoc}
     */
    public function render($template, array $parameters=array()) {
        // required bits
        $this->request = $parameters['request'];
        $this->view = $parameters['view'];
        // base properties
        $this->properties = array_merge($this->properties, $parameters);
        return $this->fetch($template, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($template) {
        $path = $this->view->getResourceResolver()->findResource($template, View::TEMPLATE);
        return !empty($path);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($template) {
        $ext = pathinfo($template, PATHINFO_EXTENSION);
        return in_array($ext, array('php', 'js'));
    }

    /**
     * Set cache.
     *
     * @param TemplateCache templateCache The cache instance.
     */
    public function setTemplateCache($templateCache) {
        $this->templateCache = $templateCache;
    }

    /**
     * Get the cache.
     *
     * @return TemplateCache The cache.
     */
    public function getTemplateCache() {
        return $this->templateCache;
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
        if (null != $this->templateCache && $this->templateCache->eligible($template)) {
            // check for cache hit
            if (null != ($result = $this->templateCache->lookup($template))) {
                return $result;
            }
        }

        // more precise would be an instance stack, i suppose
        $__fetchVars = array('template' => $template, 'variables' => $variables);

        // drop all from local scope
        unset($template);
        unset($variables);

        // prepare env
				extract($this->properties, EXTR_REFS | EXTR_SKIP);
				extract($__fetchVars['variables'], EXTR_REFS | EXTR_SKIP);
        $__fetchVars['path'] = $this->view->getResourceResolver()->findResource($__fetchVars['template'], View::TEMPLATE);

        if (empty($__fetchVars['path'])) {
            throw new ZMException('empty template filename');
        }
        ob_start();
        require $__fetchVars['path'];
        $result = ob_get_clean();

        // if we have a cache, keep it
        if (null != $this->templateCache && $this->templateCache->eligible($__fetchVars['template'])) {
            $this->templateCache->save($__fetchVars['template'], $result);
        }

        return $result;
    }

    /**
     * Resolve the given (relative) templates filename into a url.
     *
     * @param string file The file, relative to the template path.
     * @return string A url or empty string.
     */
    public function asUrl($file) {
        return $this->view->asUrl($file);
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
        return $wObj->render($this->request, $this->view);
    }

}
