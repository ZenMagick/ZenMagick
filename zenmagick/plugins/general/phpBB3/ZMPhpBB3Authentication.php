<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * phpBB3 authentication provider.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.phpbb3
 * @version $Id: ZMPhpBB3Authentication.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class ZMPhpBB3Authentication implements ZMAuthentication {
    private $passwordHash_;


    /**
     * Create instance.
     */
    function __construct() {
        $this->passwordHash_ = new PasswordHash(6, false, '$H$');
    }

    /**
     * {@inheritDoc}
     */
    public function encryptPassword($plaintext, $salt=null) { 
        return $this->passwordHash_->HashPassword($plaintext);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($plaintext, $encrypted) { 
        return $this->passwordHash_->HashPassword($plaintext);
    }

}

?>
