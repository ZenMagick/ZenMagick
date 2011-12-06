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

use zenmagick\base\ZMException;

/**
 * A view using a function to generate content.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.view
 */
class ZMFunctionView extends ZMView {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
    public function fetch($request, $template, $vars=array()) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function exists($request, $template, $type=ZMView::TEMPLATE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function asUrl($request, $template, $type=ZMView::TEMPLATE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function file2uri($request, $filename) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function path($request, $template, $type=ZMView::TEMPLATE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function find($request, $path, $regexp=null, $type=ZMView::RESOURCE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($request) {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewUtils() {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        ob_start();
        if ($this->getFunction()) {
            call_user_func($this->getFunction());
        }
        return ob_get_clean();
    }

}
