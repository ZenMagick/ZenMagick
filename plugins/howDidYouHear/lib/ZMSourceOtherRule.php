<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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


/**
 * Make other text field required if Other selected in drop down.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.howDidYouHear
 */
class ZMSourceOtherRule extends ZMRequiredRule {

    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    public function __construct($name, $msg=null) {
        parent::__construct($name, "Please enter the source where you first heard about us.");
    }


    /**
     * Validate the given request data.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if other is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        if (!array_key_exists('sourceId', $data)) {
            return false;
        }
        $sourceId = $data['sourceId'];
        if (ID_SOURCE_OTHER != $sourceId) {
            return true;
        }

        return parent::validate($request, $data);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        $js = "    new Array(function(form, name) {"
              . " var sourceId = form.elements['sourceId'];"
              . " if (!sourceId) { return false; }"
              . " if (-1 < sourceId.selectedIndex && ".ID_SOURCE_OTHER." == sourceId.options[sourceId.selectedIndex].value) {"
              . "   return zmFormValidation.isNotEmpty(form.elements['sourceOther']);"
              . " }"
              . " return true;"
              . "}";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ")";
        return $js;
    }

}
