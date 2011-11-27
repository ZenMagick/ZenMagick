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
namespace zenmagick\base\security\authentication;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

/**
 * Authentication service.
 *
 * <p>Delegates to one or more <code>Authentication</code> providers.</p>
 *
 * <p>If no provider is set as default, the first registered provider will be taken to
 * encrypt passwords.</p>.
 *
 * <p>Authentication provider are registered by tagging them as <em>zenmagick.base.security.authentication.provider</em> in the container.</p>
 *
 * @author DerManoMann
 * @package zenmagick.base.security.authentication
 */
class AuthenticationManager extends ZMObject {
    const DEFAULT_MIN_PASSWORD_LENGTH = 8;


    /**
     * Get all registered providers.
     *
     * @return array List of <code>Authentication</code> instances.
     */
    public function getProviders() {
        $providers = array();
        foreach ($this->container->findTaggedServiceIds('zenmagick.base.security.authentication.provider') as $id => $args) {
            $providers[] = $this->container->get($id);
        }

        return $providers;
    }

    /**
     * Get the default provider.
     *
     * <p>Will return the first configured provider tagged as default provider or, if none are tagged, the first provider found.</p>
     *
     * @return Authentication A provider or <code>null</code> if none are configured.
     */
    public function getDefaultProvider() {
        $firstProvider = null;
        $defaultProvider = null;
        foreach ($this->container->findTaggedServiceIds('zenmagick.base.security.authentication.provider') as $id => $args) {
            foreach ($args as $elem) {
                foreach ($elem as $key => $value) {
                    if ('default' == $key && $value) {
                        return $this->container->get($id);
                    }
                }
            }
        }
        $services = array_keys($this->container->findTaggedServiceIds('zenmagick.base.security.authentication.provider'));

        return 0 < count($services) ? $this->container->get($services[0]) : null;
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
        foreach ($this->getProviders() as $provider) {
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
        return Toolbox::random(Runtime::getSettings()->get('zenmagick.base.security.authentication.minPasswordLength', self::DEFAULT_MIN_PASSWORD_LENGTH), Toolbox::RANDOM_MIXED);
    }

}
