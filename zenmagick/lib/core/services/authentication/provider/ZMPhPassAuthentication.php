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
 * PhPass authentication provider.
 *
 * <p>Uses <a href="http://www.openwall.com/phpass/">PHPass</a>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.authentication.provider
 * @version $Id: ZMPhPassAuthentication.php 2162 2009-04-16 04:42:04Z dermanomann $
 */
class ZMPhPassAuthentication implements ZMAuthentication {
    private $passwordHash_;


    /**
     * Create instance.
     */
    function __construct() {
        $this->passwordHash_ = new PasswordHash(8, false);
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
        return phpbb_check_hash($plaintext, $encrypted);
    }

}

?>
