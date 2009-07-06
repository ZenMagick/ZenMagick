<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Store request wrapper.
 *
 * <p><strong>NOTE:</strong</strong> For the time of transition between static and instance
 * usage of request methods this will have a temp. name of <code>ZMRequestN</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc
 * @version $Id$
 */
class RequestN extends ZMRequestN {

    /**
     * Create new instance.
     *
     * @param array parameter Optional request parameter; if <code>null</code>,
     *  <code>$_GET</code> and <code>$_POST</code> will be used.
     */
    function __construct($parameter=null) {
        parent::__construct($parameter);
    }


    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the request id.
     *
     * <p>The request id is the main criteria for selecting the controller and view to process this
     * request.</p>
     *
     * @return string The value of the <code>self::REQUEST_ID</code> query parameter.
     */
    public function getRequestId() {
        return $this->getParameter('main_page');
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestId($requestId) {
        $this->setParameter(ZM_PAGE_ID, $requestId);
    }

    /**
     * {@inheritDoc}
     */
    public function getController() {
        $controller = parent::getController();
        ZMRequest::setController($controller);
        return $controller;
    }

    /**
     * {@inheritDoc}
     */
    public function setController($controller) {
        parent::setController($controller);
        ZMRequest::setController($controller);
    }

}

?>
