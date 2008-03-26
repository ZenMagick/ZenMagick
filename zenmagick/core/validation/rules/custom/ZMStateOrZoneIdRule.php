<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Check for either state or zone.
 *
 * <p>This rule will only work if the address object is validated rather than
 * just the request map.</p>
 *
 * @author mano
 * @package org.zenmagick.validation.rules.custom
 * @version $Id$
 */
class ZMStateOrZoneIdRule extends ZMRule {


    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function ZMStateOrZoneIdRule($name, $msg=null) {
        parent::__construct($name, "Please enter a state.", $msg);
    }

    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function __construct($name, $msg=null) {
        $this->ZMStateOrZoneIdRule($name, $msg);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        if (isset($req['__obj'])) {
            $address = $req['__obj'];

            if (!zm_setting('isAccountState')) {
                return true;
            }

            // TODO: make sure that zone is actually required!
            return !zm_is_empty($address->getState()) || 0 != $address->getZoneId();
        }

        return true;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    function toJSString() {
        return '';
    }

}

?>
