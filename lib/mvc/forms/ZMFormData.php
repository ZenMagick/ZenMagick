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

use zenmagick\base\Beans;
use zenmagick\base\ZMObject;

/**
 * Basic form data container.
 *
 * <p>If no form class is configured for an url, the form will be stored in an <code>array()</code>.
 * If custom pre-processing of form data is required, a custom container class extending <code>ZMFormData</code>
 * can be used.</p>
 *
 * <p>The default implementation of <code>populate($request)</code> will just populate the form container instance
 * with all request data. Custom implementations are free to override/extend <code>populate($request)</code> to hook
 * up their own population code.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.forms
 */
class ZMFormData extends ZMObject {

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
     * Populate this form.
     *
     * <p>Populate this <em>bean</em> with all request parameters.</p>
     *
     * @param ZMRequest request The request to process.
     */
    public function populate($request) {
        Beans::setAll($this, $request->getParameterMap(), null);
    }

}
