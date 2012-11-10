<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\ZenCartBundle\Utils;

use ZenMagick\Base\Toolbox;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Authentication provider compatible with zencart generated passwords.
 *
 * @author DerManoMann
 */
class ZenCartAuthenticationProvider implements PasswordEncoderInterface
{

    /**
     * {@inheritDoc}
     */
    public function encodePassword($raw, $salt = null)
    {
        $password = '';
        for ($i=0; $i<10; $i++) {
            $password .= Toolbox::random(Toolbox::RANDOM_MIXED);
        }

        $salt = substr(md5($password), 0, 2);
        $password = md5($salt . $raw) . ':' . $salt;

        return $password;
    }

    /**
     * {@inheritDoc}
     */
    public function isPasswordValid($encoded, $raw, $salt = null)
    {
        if (!empty($raw) && !empty($encoded)) {
            $stack = explode(':', $encoded);
            if (sizeof($stack) != 2) return false;
            if (md5($stack[1] . $raw) == $stack[0]) {
                return true;
            }
        }

        return false;
    }

}
