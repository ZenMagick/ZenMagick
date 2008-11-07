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
 *
 * $Id: security.php 1113 2008-05-16 00:30:49Z DerManoMann $
 */
?>
<?php

    /**
     * Sanitize the given value.
     *
     * @package org.zenmagick.security
     * @param mixed value A string or array.
     * @return mixed A sanitixed version.
     * @deprecated use ZMTools instead
     */
    function zm_sanitize($value) {
        return ZMTools::sanitize($value);
    }


    /**
     * Encrypt the given password.
     *
     * @package org.zenmagick.security
     * @param string clear The clear text password to encrypt.
     * @return string The encrypted password.
     * @deprecated use ZMAuthenticationManager instead
     */
    function zm_encrypt_password($clear) { return zen_encrypt_password($clear); }


    /**
     * Validate the given clear text password against the encrypted one.
     *
     * @package org.zenmagick.security
     * @param string clear The clear text password to encrypt.
     * @param string encrypted The encrypted password.
     * @return boolean <code>true</code> if the passwords match, <code>false</code> if not.
     * @deprecated use ZMAuthenticationManager instead
     */
    function zm_validate_password($clear, $encrypted) { 
        return zen_validate_password($clear, $encrypted) 
            || (function_exists('zm_custom_validate_password') && zm_custom_validate_password($clear, $encrypted));
    }


    /**
     * Generate a random value.
     *
     * @package org.zenmagick.security
     * @param int length The length of the random value.
     * @param string type Optional type; valid values are: 'mixed', 'chars' and 'digits'.
     * @return string The random string.
     * @deprecated use ZMAuthenticationManager instead
     */
    function zm_random_value($length, $type='mixed') { return zen_create_random_value($length, $type); }


    /**
     * Generate a new random password.
     *
     * @package org.zenmagick.security
     * @return string The new password.
     * @deprecated use ZMAuthenticationManager instead
     */
    function zm_new_password() { return zen_create_random_value(ZMSettings::get('minPasswordLength'), 'mixed'); }

?>
