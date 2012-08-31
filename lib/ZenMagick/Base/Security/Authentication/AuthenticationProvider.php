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
namespace ZenMagick\Base\Security\Authentication;


/**
 * Provider of authentication service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface AuthenticationProvider {

    /**
     * Encrypt a given password.
     *
     * @param string plaintext The plain text password.
     * @param string salt Optional salt to improve encryption; default is <code>null</code>.
     * @return string The encrypted password.
     */
    public function encryptPassword($plaintext, $salt=null);

    /**
     * Validate the given clear text password against the encrypted one.
     *
     * @param string plaintext The plain text password.
     * @param string encrypted The encrypted password.
     * @return boolean <code>true</code> if the plain text password matches the encrypted, <code>false</code> if not.
     */
    public function validatePassword($plaintext, $encrypted);

}
