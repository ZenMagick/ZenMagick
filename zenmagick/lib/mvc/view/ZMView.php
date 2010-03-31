<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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


/**
 * Base implementation of the <code>ZMView</code> interface.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 * @version $Id$
 */
abstract class ZMView extends ZMObject {
    private $vars_;
    private $viewId_;
    private $template_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->vars_ = array();
        $this->viewId_ = null;
        $this->template_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Make a variable (value) available under the given name.
     *
     * @param string name The variable name.
     * @param mixed value The value.
     */
    public function setVar($name, $value) {
        $this->vars_[$name] = $value;
    }

    /**
     * Set multiple variables.
     *
     * @param array vars A map of name/value pairs.
     */
    public function setVars($vars) {
        $this->vars_ = array_merge($this->vars_, $vars);
    }

    /**
     * Get all available variables in this view.
     *
     * @return array A name/value map.
     */
    public function getVars() {
        return $this->vars_;
    }

    /**
     * Check if this view is valid.
     *
     * <p>This is optional and it is up to the specific subclass to implement as appropriate.</p>
     *
     * @return boolean <code>true</code> if the view is valid.
     */
    public function isValid() {
        return true;
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
        return $this->fetch($request, $this->getTemplate().ZMSettings::get('zenmagick.mvc.templates.ext', '.php'));
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
        return ZMSettings::get('zenmagick.mvc.html.contentType', 'text/html');
    }

    /**
     * Get the content encoding.
     *
     * <p>Return the value of the setting <em>zenmagick.mvc.html.charset</em> or <em>UTF-8</em> as default.</p>
     *
     * @return string The content encoding.
     */
    public function getEncoding() {
        return ZMSettings::get('zenmagick.mvc.html.charset', 'UTF-8');
    }

    /**
     * Fetch/generate the contents of the given template.
     *
     * @param request The current request.
     * @param string template The template name.
     * @return string The contents.
     */
    public abstract function fetch($request, $template);

}
