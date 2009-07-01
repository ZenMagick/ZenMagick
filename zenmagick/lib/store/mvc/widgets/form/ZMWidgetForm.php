<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Utility class for HTML forms that use widgets.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.widgets.form
 * @version $Id: ZMWidgetForm.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMWidgetForm extends ZMObject {

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
     * Process a request data array with respect to widget specific conversions etc.
     *
     * <p>One example of why this is useful is that checkbox data will be absent from the data
     * if unchecked. The responsible widget will update the data accordingly, depending on
     * automatically generated hidden fields, etc.</p>
     *
     * @param array data The request data.
     * @param array widgets A list of widgets.
     * @return array The processed data.
     */
    public static function processRequest($data, $widgets) {
        foreach ($widgets as $widget) {
            if ($widget instanceof ZMFormWidget) {
                $data = $widget->handleFormData($data);
            }
        }
        return $data;
    }

}

?>
