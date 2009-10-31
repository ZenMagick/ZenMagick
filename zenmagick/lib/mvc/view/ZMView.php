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
class ZMView extends ZMObject {
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
     * {@inheritDoc}
     */
    public function setVar($name, $value) {
        $this->vars_[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function setVars($vars) {
        $this->vars_ = array_merge($this->vars_, $vars);
    }

    /**
     * {@inheritDoc}
     */
    public function getVars() {
        return $this->vars_;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid() {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        throw new ZMException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getViewId() {
        return $this->viewId_;
    }

    /**
     * {@inheritDoc}
     */
    public function setViewId($viewId) {
        $this->viewId_ = $viewId;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate() {
        return $this->template_;
    }

    /**
     * {@inheritDoc}
     */
    public function setTemplate($template) {
        $this->template_ = $template;
    }

    /**
     * {@inheritDoc}
     */
    public function getContentType() {
        return 'text/html';
    }

    /**
     * {@inheritDoc}
     */
    public function getEncoding() {
        return zm_i18n('HTML_CHARSET');
    }

}

?>
