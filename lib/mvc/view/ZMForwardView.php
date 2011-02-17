<?php
/*
 * ZenMagick - Another PHP framework.
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
 * Forward view.
 *
 * <p>This will forward the request to the given controller without a redirect.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 */
class ZMForwardView extends ZMView {
    private $requestId_;


    /**
     * Create a new forward view.
     */
    function __construct() {
        parent::__construct();
        $this->requestId_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get the request id of the redirect.
     *
     * <p>If not set, this will default to the template name (compatibility mode).</p>
     *
     * @return string The request id.
     */
    public function getRequestId() {
        return $this->requestId_;
    }

    /**
     * Set the request id of the redirect.
     *
     * @param string requestId The request id.
     */
    public function setRequestId($requestId) {
        $this->requestId_ = $requestId;
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
        return !ZMLangUtils::isEmpty($this->getRequestId());
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
        $req = ZMBeanUtils::getBean('Request');
        $req->setParameterMap($request->getParameterMap(false));
        $req->setRequestId($this->getRequestId());
        // keep reference to original request
        $req->setParameter('rootRequestId', $request->getRequestId());

        ZMDispatcher::dispatch($req);
        return null;
    }

}
