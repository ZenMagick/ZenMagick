<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
     * Get instances of all registered providers.
     *
     * <p>This method is part of the internal optimizations to allow to register class names,
     * rather than actual instances.</p>
     *
     * @param string class Optional parameter if only a specific implementation is required; default is <code>null</code>.
     * @return array List of <code>ZMAuthentication</code> instances.
     */
    protected function getImplementations($class=null) {
        $keys = null != $class ? array($class) : array_keys($this->providers_);
        $implementations = array();
        foreach ($keys as $key) {
            $implementation = $this->providers_[$key];
            if (!is_object($implementation)) {
                $implementation = ZMLoader::make($key);
                if (!($implementation instanceof ZMAuthentication)) {
                    throw new ZMException('invalid auth provider: '. get_class($implementation));
                }
                $this->providers_[$key] = $implementation;
            }
            $implementations[] = $implementation;
        }

        return $implementations;
    }

    /**
     * Add an authentication provider (class).
     *
     * @param string auth The authentication implementation class.
     * @param boolean default Optional flag make <em>auth</em> the default provider; default is <code>false</code>.
     */
    public function addProvider($auth, $default=false) {
        if (!array_key_exists($auth, $this->providers_)) {
            // keep them unique
            $this->providers_[$auth] = $auth;
        }

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
        if (0 < count($this->providers_)) {
            $key = $this->default_;
            if (null == $key) {
                $keys = array_keys($this->providers_);
                $key = $keys[0];
            }
            $arr = $this->getImplementations($key);
            return $arr[0];
        }

        return null;
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
        foreach ($this->getImplementations() as $provider) {
            if ($provider->validatePassword($plaintext, $encrypted)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a new random password.
     *
     * @return string The new password.
     */
    public function mkPassword() {
        return ZMTools::random(ZMSettings::get('minPasswordLength'), ZMTools::RANDOM_MIXED);
    }

}

?>
