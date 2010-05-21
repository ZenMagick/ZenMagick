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
 * Ajax response for JSON.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.ajax.formats
 * @version $Id$
 */
class ZMAjaxResponseJSON extends ZMAbstractAjaxResponse {

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
     *
     * <p>Create <code>JSON</code> response.</p>
     */
    public function createResponse($controller) {
        $resp = array(
            'status' => $this->status_,
            'messages' => $this->messages_,
            'properties' => $this->properties_,
            'data' => ZMAjaxUtils::flattenObject($this->data_)
        );

        $json = json_encode($resp);
        $controller->setContentType('text/plain');
        if (ZMSettings::get('zenmagick.mvc.json.header')) { header("X-JSON: ".$json); }
        if (ZMSettings::get('zenmagick.mvc.json.echo')) { echo $json; }
        return $json;
    }

}
