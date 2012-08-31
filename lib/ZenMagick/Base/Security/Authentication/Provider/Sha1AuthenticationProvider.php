<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\Base\Security\Authentication\Provider;

use ZenMagick\Base\Security\Authentication\AuthenticationProvider;

/**
 * Sha1 authentication provider.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Sha1AuthenticationProvider implements AuthenticationProvider {
    /** Number of characters taken from the given salt to encrypt the password. */
    const SALT_LENGTH = 9;


    /**
     * {@inheritDoc}
     */
    public function encryptPassword($plaintext, $salt=null) {
        if (null === $salt) {
            $salt = substr(md5(uniqid(rand(), true)), 0, self::SALT_LENGTH);
        } else {
            // expect the encrypted password to extract the original salt
            $salt = substr($salt, 0, self::SALT_LENGTH);
        }

        return $salt . sha1($salt . $plaintext);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($plaintext, $encrypted) {
        return $encrypted == $this->encryptPassword($plaintext, $encrypted);
    }

}
