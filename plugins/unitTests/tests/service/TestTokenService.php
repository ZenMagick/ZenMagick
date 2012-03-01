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

/**
 * Test <code>TokenServics</code>.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestTokenService extends ZMTestCase {

    /**
     * Test get new token.
     */
    public function testGetNewToken() {
        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        $tokenService = $this->container->get('tokenService');
        // create token
        $token = $tokenService->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        // make sure it is valid (do not expire)
        $this->assertNotNull($tokenService->validateHash($resource, $token->getHash(), false));

        // make sure it is still valid
        $this->assertNotNull($tokenService->validateHash($resource, $token->getHash(), true));

        // check that is it not valid any more
        $this->assertNull($tokenService->validateHash($resource, $token->getHash(), true));
    }

    /**
     * Test expired.
     */
    public function testExpired() {
        $resource = 'abc';
        $lifetime = 2; // 2 seconds

        $tokenService = $this->container->get('tokenService');
        // create token
        $token = $tokenService->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        // make sure it is valid (do not expire)
        $this->assertNotNull($tokenService->validateHash($resource, $token->getHash(), false));

        // wait a bit
        sleep($lifetime+1);

        // check that is it not valid any more
        $this->assertNull($tokenService->validateHash($resource, $token->getHash(), true));
    }

    /**
     * Test clear expired.
     */
    public function testClearExpired() {
        $this->container->get('tokenService')->clear(false);
    }

    /**
     * Test update.
     */
    public function testUpdateToken() {
        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        $tokenService = $this->container->get('tokenService');
        // create token
        $token = $tokenService->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        // make sure it is valid (do not expire)
        $this->assertNotNull($tokenService->validateHash($resource, $token->getHash(), false));

        // copy as update will update $token
        $tokenExpiry = $token->getExpires();
        $tokenService->updateToken($token, 7*$lifetime);
        $update = $tokenService->validateHash($resource, $token->getHash(), false);
        $this->assertNotNull($update);
        $this->assertEqual($update->getHash(), $token->getHash());
        $this->assertEqual($update->getResource(), $token->getResource());
        $updateExpiry = $update->getExpires();
        $this->assertTrue($tokenExpiry < $updateExpiry);
    }

    /**
     * Test get token for resource.
     */
    public function testgetTokenForResource() {
        $tokenService = $this->container->get('tokenService');

        $tokenService->clear(true);

        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        // create single
        $token = $tokenService->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        $this->assertEqual(1, count($tokenService->getTokenForResource($resource)));

        // create second
        $token = $tokenService->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        $this->assertEqual(2, count($tokenService->getTokenForResource($resource)));
    }

    /**
     * Test get token for hash.
     */
    public function testgetTokenForHash() {
        $tokenService = $this->container->get('tokenService');

        $tokenService->clear(true);

        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        // create single
        $token = $tokenService->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        $this->assertNotNull($tokenService->getTokenForHash($token->getHash()));

        // clear all
        $tokenService->clear(true);
        $this->assertNull($tokenService->getTokenForHash($token->getHash()));
    }

}
