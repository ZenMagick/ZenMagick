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
        $manager = new ZMAuthenticationManager();
        //$this->assertTrue($manager->getDefaultProvider() instanceof ZMSha1Authentication);
        $manager->addProvider('ZMZenCartAuthentication', true);
        $this->assertNotNull($manager->getDefaultProvider());

        // check zc encryypted password
        $zcProvider = new ZMZenCartAuthentication();
        $zcpwd = 'foobar';
        $zcenc = $zcProvider->encryptPassword($zcpwd);
        $this->assertTrue($manager->validatePassword($zcpwd, $zcenc));

        // check that manager uses proper default provider to encrypt
        $manpwd = 'dohbar';
        $manenc = $manager->encryptPassword($manpwd);
        $this->assertTrue($zcProvider->validatePassword($manpwd, $manenc));
    }

    /**
     * Test manager multi provider.
     */
    public function testManagerMulti() {
        $manager = new ZMAuthenticationManager();
        //$this->assertTrue($manager->getDefaultProvider() instanceof ZMSha1Authentication);
        $manager->addProvider('ZMZenCartAuthentication');
        $this->assertNotNull($manager->getDefaultProvider());
        $manager->addProvider('ZMSha1Authentication', true);
        $this->assertTrue($manager->getDefaultProvider() instanceof ZMSha1Authentication);

        // check zc encryypted password
        $zcProvider = new ZMZenCartAuthentication();
        $zcpwd = 'foobar';
        $zcenc = $zcProvider->encryptPassword($zcpwd);
        $this->assertTrue($manager->validatePassword($zcpwd, $zcenc));

        // check sha1 encryypted password
        $sha1Provider = new ZMSha1Authentication();
        $sha1pwd = 'boofar';
        $sha1enc = $sha1Provider->encryptPassword($sha1pwd);
        $this->assertTrue($manager->validatePassword($sha1pwd, $sha1enc));

        // check that manager uses proper default provider to encrypt
        $manpwd = 'dohbar';
        $manenc = $manager->encryptPassword($manpwd);
        $this->assertTrue($sha1Provider->validatePassword($manpwd, $manenc));
    }

    /**
     * Test providers.
     */
    public function testProviders() {
        $implementations = array('ZMZenCartAuthentication', 'ZMSha1Authentication');
        foreach ($implementations as $class) {
            $provider = Beans::getBean($class);
            if ($this->assertNotNull($provider, '%s: '.$class)) {
                $plaintext = 'foobar';
                $encrypted = $provider->encryptPassword($plaintext);
                $this->assertTrue($plaintext != $encrypted);
                $this->assertNotNull($encrypted);
                $this->assertTrue($provider->validatePassword($plaintext, $encrypted));
            }
        }
    }

}
