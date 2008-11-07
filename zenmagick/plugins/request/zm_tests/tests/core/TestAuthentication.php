<?php

/**
 * Test authentication.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestAuthentication extends ZMTestCase {

    /**
     * Test manager.
     */
    public function testManager() {
        $manager = ZMLoader::make('ZMAuthenticationManager');
        $this->assertNull($manager->getDefaultProvider());
        $manager->addProvider(ZMLoader::make('ZMZenCartAuthentication'));
        $this->assertNotNull($manager->getDefaultProvider());
        $manager->addProvider(ZMLoader::make('ZMSha1Authentication'), true);
        $this->assertTrue($manager->getDefaultProvider() instanceof ZMSha1Authentication);

        // check zc encryypted password
        $zcProvider = ZMLoader::make('ZMZenCartAuthentication');
        $zcpwd = 'foobar';
        $zcenc = $zcProvider->encryptPassword($zcpwd);
        $this->assertTrue($manager->validatePassword($zcpwd, $zcenc));

        // check sha1 encryypted password
        $sha1Provider = ZMLoader::make('ZMSha1Authentication');
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
            $provider = ZMLoader::make($class);
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

?>
