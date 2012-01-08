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
namespace zenmagick\base\security\authentication\provider;

use zenmagick\base\security\authentication\AuthenticationProvider;


/**
 * PhPass authentication provider.
 *
 * <p>Uses <a href="http://www.openwall.com/phpass/">PHPass</a>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.security.authentication.provider
 */
class PhPassAuthenticationProvider implements AuthenticationProvider {
    private $passwordHash_;


    /**
     * Create instance.
     */
    function __construct() {
        $this->passwordHash_ = new \PasswordHash(8, false);
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
        return $this->passwordHash_->CheckPassword($plaintext, $encrypted);
    }

}
