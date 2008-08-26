<?php

/**
 * Test <code>ZMTokens</code>.
 *
 * @package org.zenmagick.plugins.zm_token.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMTokens extends UnitTestCase {

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
        ZMTokens::instance()->clearExpired();
    }

}

?>
