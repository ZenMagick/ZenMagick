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
 */
?>
<?php


/**
 * Authentication service.
 *
 * <p>Delegates to one or more <code>ZMAuthentication</code> providers.</p>
 *
 * <p>If no provider is set as default, the first registered provider will be taken to
 * encrypt passwords.</p>.
 *
 * @author DerManoMann
 * @package org.zenmagick.authentication
 * @version $Id$
 */
class ZMAuthenticationManager extends ZMObject {
    const RANDOM_DIGITS = 'digits';
    const RANDOM_CHARS = 'chars';
    const RANDOM_MIXED = 'mixed';
    private $providers_;
    private $default_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->providers_ = array();
        $this->default_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('AuthenticationManager');
    }


    /**
     * Add an authentication provider (class).
     *
     * @param ZMAuthentication auth The authentication implementation.
     * @param boolean default Optional flag make <em>auth</em> the default provider; default is <code>false</code>.
     */
    public function addProvider($auth, $default=false) {
        $class = get_class($auth);
        if (!($auth instanceof ZMAuthentication)) {
            throw ZMLoader::make('ZMException', 'invalid auth provider: '. $class);
        }

        $this->providers_[$class] = $auth;
        if ($default) {
            $this->default_ = $auth;
        }
    }

    /**
     * Get the default provider.
     *
     * @return ZMAuthentication A provider or <code>null</code> if none are configured.
     */
    public function getDefaultProvider() {
        if (null == $this->default_ && 0 < count($this->providers_)) {
            $keys = array_keys($this->providers_);
            $this->default_ = $this->providers_[$keys[0]];
        }

        return $this->default_;
    }

    /**
     * Encrypt a given password.
     *
     * @param string plaintext The plain text password.
     * @param string salt Optional salt to improve encryption; default is <code>null</code>.
     * @return string The encrypted password.
     */
    public function encryptPassword($plaintext, $salt=null) {
        return $this->getDefaultProvider()->encryptPassword($plaintext, $salt);
    }

    /**
     * Validate the given clear text password against the encrypted one.
     *
     * @param string plaintext The plain text password.
     * @param string encrypted The encrypted password.
     * @return boolean <code>true</code> if the plain text password matches the encrypted, <code>false</code> if not.
     */
    public function validatePassword($plaintext, $encrypted) {
        foreach ($this->providers_ as $provider) {
            if ($provider->validatePassword($plaintext, $encrypted)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate a random value.
     *
     * @param int length The length of the random value.
     * @param string type Optional type; predefined values are: <em>mixed</em>, <em>chars</em> and <em>digits</em>; default is <em>mixed</em>.
     *  Any other value will be used as the valid character range.
     * @return string The random string.
     */
    public static function random($length, $type='mixed') { 
        static $types	=	array(
            self::RANDOM_DIGITS => '0123456789', 
            self::RANDOM_CHARS => 'abcdefghijklmnopqrstuvwxyz',
            self::RANDOM_MIXED => '0123456789abcdefghijklmnopqrstuvwxyz',
        );
        $chars = array_key_exists($type, $types) ? $types[$type] : $type;
        $max=	strlen($chars) - 1;
        $token = '';
        $name = session_name();
        for ($ii=0; $ii < $length; ++$ii) {
            $token .=	$chars[(rand(0, $max))];
        }

        return md5($token.$name);
    }

    /**
     * Generate a new random password.
     *
     * @return string The new password.
     */
    public function mkPassword() {
        return self::random(ZMSettings::get('minPasswordLength'), self::RANDOM_MIXED);
    }

}

?>
