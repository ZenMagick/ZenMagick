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
 * Basic form data container.
 *
 * <p>Extending from this class allows to restrict the fields taken from the request to a given
 * list via <code>addFields()</code>.</p>
 *
 * <p>If no fields are added at all, all request parameter will be 
 * used.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.forms
 * @version $Id$
 */
class ZMFormData extends ZMObject {
    protected $fields_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->fields_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Limit populating this form bean to the given fields.
     *
     * @param mixed fields Either an array or comma separated list of field names.
     */
    public function addFields($fields) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $this->fields_ = array_merge($this->fields_, $fields);
    }

    /**
     * Populate this form.
     *
     * @param ZMRequest request The request to process.
     */
    public function populate($request) {
        $fields = (0 < count($this->fields_)) ?  $this->fields_ : null;
        ZMBeanUtils::setAll($this, $request->getParameterMap(), $fields);
    }

}
