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

use Phpass\Hash;
use ZenMagick\Base\Security\Authentication\AuthenticationProvider;

/**
 * PhPass 2.0 authentication provider.
 *
 * <p>Uses <a href="https://github.com/rchouinard/phpass">Phpass</a>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PhPassAuthenticationProvider implements AuthenticationProvider
{
    protected $hash;

    /**
     * Create instance.
     */
    public function __construct()
    {
        $this->hash = new Hash();
    }

    /**
     * {@inheritDoc}
     */
    public function encryptPassword($plaintext, $salt=null)
    {
        return $this->hash->hashPassword($plaintext);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($plaintext, $encrypted)
    {
        return $this->hash->checkPassword($plaintext, $encrypted);
    }

}
