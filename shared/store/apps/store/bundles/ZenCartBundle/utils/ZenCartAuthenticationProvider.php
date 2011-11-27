<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace apps\store\bundles\ZenCartBundle\utils;

use zenmagick\base\Toolbox;
use zenmagick\base\security\authentication\AuthenticationProvider;

/**
 * Authentication provider compatible with zencart generated passwords.
 *
 * @author DerManoMann
 * @package apps.store.bundles.ZenCartBundle.utils
 */
class ZenCartAuthenticationProvider implements AuthenticationProvider {

    /**
     * {@inheritDoc}
     */
    public function encryptPassword($plaintext, $salt=null) {
        $password = '';
        for ($i=0; $i<10; $i++) {
            $password .= Toolbox::random(Toolbox::RANDOM_MIXED);
        }

        $salt = substr(md5($password), 0, 2);
        $password = md5($salt . $plaintext) . ':' . $salt;

        return $password;
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($plaintext, $encrypted) {
        if (!empty($plaintext) && !empty($encrypted)) {
            $stack = explode(':', $encrypted);
            if (sizeof($stack) != 2) return false;
            if (md5($stack[1] . $plaintext) == $stack[0]) {
                return true;
            }
        }

        return false;
    }

}
