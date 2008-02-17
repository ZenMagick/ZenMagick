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
 * @version $Id: views.php 426 2007-10-25 23:01:11Z DerManoMann $
 */
?>
<?php

    /**
     * Custom password validation using the master password.
     *
     * @package org.zenmagick.plugins.zm_master_password
     * @param string clear The clear text password to encrypt.
     * @param string encrypted The encrypted password.
     * @return boolean <code>true</code> if the passwords match, <code>false</code> if not.
     */
    function zm_custom_validate_password($clear, $encrypted) {
    global $zm_master_password;

        $masterPassword = $zm_master_password->get('masterPassword');

        return !zm_is_empty($masterPassword) && zen_validate_password($clear, $masterPassword);
    }

?>
