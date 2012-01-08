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

use zenmagick\base\Runtime;
use zenmagick\base\settings\Settings;
use zenmagick\base\Toolbox;
use zenmagick\base\security\authentication\AuthenticationProvider;

/**
 * Test authentication manager and provider.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestAuthenticationManager extends ZMTestCase {

    /**
     * Test getProvider
     */
    public function testGetProviders() {
        $authenticationManager = $this->container->get('authenticationManager');
        $this->assertEqual(4, count($authenticationManager->getProviders()));
    }

    /**
     * Test default provider
     */
    public function testDefaultProvider() {
        $authenticationManager = $this->container->get('authenticationManager');
        $defaultProvider = $authenticationManager->getDefaultProvider();
        $this->assertNotNull($defaultProvider);
        $password = 'foo';
        $hashed = $defaultProvider->encryptPassword($password);
        $this->assertTrue($defaultProvider->validatePassword($password, $hashed));
        // also test that the authentication manager uses the default provider to validate
        $this->assertTrue($authenticationManager->validatePassword($password, $hashed));
    }

    /**
     * Test all provider
     */
    public function testAllProvider() {
        $authenticationManager = $this->container->get('authenticationManager');
        $password = 'foo';
        foreach ($authenticationManager->getProviders() as $provider) {
            $this->assertTrue($provider instanceof AuthenticationProvider);
            if ($provider instanceof ZMMasterPasswordAuthenticationProvider) {
                continue;
            }
            $hashed = $provider->encryptPassword($password);
            $this->assertTrue($provider->validatePassword($password, $hashed));
            // also test that the authentication manager can validate the password
            $this->assertTrue($authenticationManager->validatePassword($password, $hashed));
        }
    }

}
