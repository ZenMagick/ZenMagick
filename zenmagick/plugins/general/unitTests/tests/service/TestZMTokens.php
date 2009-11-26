<?php

/**
 * Test <code>ZMTokens</code>.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMTokens.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TestZMTokens extends ZMTestCase {

    /**
     * Test get new token.
     */
    public function testGetNewToken() {
        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        // create token
        $token = ZMTokens::instance()->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        // make sure it is valid (do not expire)
        $this->assertNotNull(ZMTokens::instance()->validateHash($resource, $token->getHash(), false));

        // make sure it is still valid
        $this->assertNotNull(ZMTokens::instance()->validateHash($resource, $token->getHash(), true));

        // check that is it not valid any more
        $this->assertNull(ZMTokens::instance()->validateHash($resource, $token->getHash(), true));
    }

    /**
     * Test expired.
     */
    public function testExpired() {
        $resource = 'abc';
        $lifetime = 2; // 2 seconds

        // create token
        $token = ZMTokens::instance()->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        // make sure it is valid (do not expire)
        $this->assertNotNull(ZMTokens::instance()->validateHash($resource, $token->getHash(), false));

        // wait a bit
        sleep($lifetime+1);

        // check that is it not valid any more
        $this->assertNull(ZMTokens::instance()->validateHash($resource, $token->getHash(), true));
    }

    /**
     * Test clear expired.
     */
    public function testClearExpired() {
        ZMTokens::instance()->clear(false);
    }

    /**
     * Test update.
     */
    public function testUpdateToken() {
        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        // create token
        $token = ZMTokens::instance()->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        // make sure it is valid (do not expire)
        $this->assertNotNull(ZMTokens::instance()->validateHash($resource, $token->getHash(), false));

        // copy as update will update $token
        $tokenExpiry = $token->getExpires();
        ZMTokens::instance()->updateToken($token, 7*$lifetime);
        $update = ZMTokens::instance()->validateHash($resource, $token->getHash(), false);
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
        ZMTokens::instance()->clear(true);

        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        // create single 
        $token = ZMTokens::instance()->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        $this->assertEqual(1, count(ZMTokens::instance()->getTokenForResource($resource)));

        // create second 
        $token = ZMTokens::instance()->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        $this->assertEqual(2, count(ZMTokens::instance()->getTokenForResource($resource)));
    }

    /**
     * Test get token for hash.
     */
    public function testgetTokenForHash() {
        ZMTokens::instance()->clear(true);

        $resource = 'abc';
        $lifetime = 24*60*60; // 1 day

        // create single 
        $token = ZMTokens::instance()->getNewToken($resource, $lifetime);
        $this->assertNotNull($token);

        $this->assertNotNull(ZMTokens::instance()->getTokenForHash($token->getHash()));

        // clear all
        ZMTokens::instance()->clear(true);
        $this->assertNull(ZMTokens::instance()->getTokenForHash($token->getHash()));
    }

}

?>
