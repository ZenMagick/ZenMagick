<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Method controller.
 *
 * @package org.zenmagick.plugins.methodController
 * @author DerManoMann
 */
class ZMMethodController extends ZMController {

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
     * Custom request processing <em>foo</em>.
     */
    public function foo($request) {
        echo 'foo';
        // no layout/view output
        return null;
    }

    /**
     * Custom request processing <em>bar</em>.
     */
    public function bar($request) {
        // no layout, default view
        return $this->findView();
    }

    /**
     * Custom request processing <em>xform</em>.
     */
    public function xform($request) {
        // HTTP method
        switch ($request->getMethod()) {
        case 'GET': return $this->xform_get($request);
        case 'POST': return $this->xform_post($request);
        }

        // rid independant error page (mapped under null:)
        ZMMessages::instance()->error(_zm('Invalid request method'));
        return $this->findView('error');
    }

    /**
     * Process xform GET.
     */
    protected function xform_get($request) {
        // display default 
        return $this->findView();
    }

    /**
     * Process xform POST.
     */
    protected function xform_post($request) {
        $name = $request->getParameter('name');
        $viewId = null;
        if (empty($name)) {
            ZMMessages::instance()->error(_zm('Please enter a name'));
        } else {
            ZMMessages::instance()->success(_zm('The name entered was: '.$name));
            $viewId = 'success';
        }

        // display view based on processing, add name as custom view data
        // NOTE: custom data is not available if a redirect view is used
        return $this->findView($viewId, array('enteredName' => $name));
    }

}
