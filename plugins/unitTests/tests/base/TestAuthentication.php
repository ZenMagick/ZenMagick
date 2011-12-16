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

use zenmagick\base\Beans;
use zenmagick\base\security\authentication\AuthenticationProvider;
use zenmagick\base\security\authentication\provider\Sha1AuthenticationProvider;
use apps\store\bundles\ZenCartBundle\utils\ZenCartAuthenticationProvider;

/**
 * Test authentication.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestAuthentication extends ZMTestCase {

    /**
     * Test manager single provider.
     */
    public function testManagerSingle() {
        $authenticationManager = $this->container->get('authenticationManager');
        $this->assertNotNull($authenticationManager->getDefaultProvider());

        // check zc encryypted password
        $zcProvider = new ZenCartAuthenticationProvider();
        $zcpwd = 'foobar';
        $zcenc = $zcProvider->encryptPassword($zcpwd);
        $this->assertTrue($authenticationManager->validatePassword($zcpwd, $zcenc));

        // check that manager uses proper default provider to encrypt
        $manpwd = 'dohbar';
        $manenc = $authenticationManager->encryptPassword($manpwd);
        $this->assertTrue($zcProvider->validatePassword($manpwd, $manenc));
    }

    /**
     * Test manager multi provider.
     */
    public function testManagerMulti() {
        $authenticationManager = $this->container->get('authenticationManager');
        $this->assertNotNull($authenticationManager->getDefaultProvider());

        // check zc encryypted password
        $zcProvider = new ZenCartAuthenticationProvider();
        $zcpwd = 'foobar';
        $zcenc = $zcProvider->encryptPassword($zcpwd);
        $this->assertTrue($authenticationManager->validatePassword($zcpwd, $zcenc));

        // check sha1 encryypted password
        $sha1Provider = new Sha1AuthenticationProvider();
        $sha1pwd = 'boofar';
        $sha1enc = $sha1Provider->encryptPassword($sha1pwd);
        $this->assertTrue($authenticationManager->validatePassword($sha1pwd, $sha1enc));

        // check that authenticationManager uses proper default provider to encrypt
        $manpwd = 'dohbar';
        $manenc = $authenticationManager->encryptPassword($manpwd);
        $this->assertTrue($zcProvider->validatePassword($manpwd, $manenc));
    }

    /**
     * Test providers.
     */
    public function testProviders() {
        $authenticationManager = $this->container->get('authenticationManager');
        foreach ($authenticationManager->getProviders() as $provider) {
            if ($this->assertTrue($provider instanceof AuthenticationProvider)) {
                $plaintext = 'foobar';
                try {
                    $encrypted = $provider->encryptPassword($plaintext);
                    $this->assertTrue($plaintext != $encrypted);
                    $this->assertNotNull($encrypted);
                    $this->assertTrue($provider->validatePassword($plaintext, $encrypted));
                } catch (Exception $e) {
                    // not all support encrypting
                }
            }
        }
    }

}
