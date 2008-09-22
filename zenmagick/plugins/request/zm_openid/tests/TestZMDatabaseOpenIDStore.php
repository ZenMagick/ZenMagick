<?php

/**
 * Test OpenID store implementation.
 *
 * @package org.zenmagick.plugins.zm_openid
 * @author DerManoMann
 * @version $Id$
 */
class TestZMDatabaseOpenIDStore extends UnitTestCase {

    /**
     * Test store.
     */
    public function testStore() {
        $store = ZMLoader::make('ZMDatabaseOpenIDStore');
        $this->assertNotNull($store);
        $ass = new Auth_OpenID_Association('foo', 'secret', 1, (time()+100000), 'HMAC-SHA1');
        $store->storeAssociation('bar', $ass);
        $bar = $store->getAssociation('bar');
        $this->assertNotNull($bar);

        // remove non matching handle
        $store->removeAssociation('bar', 'foo2');
        $bar = $store->getAssociation('bar');
        $this->assertNotNull($bar);

        // remove all
        $store->removeAssociation('bar', 'foo');
        $bar = $store->getAssociation('bar');
        $this->assertNull($bar);

        // just try
        $store->useNonce('bar', time(), 'secret');

        $store->cleanupNonces();
        $store->cleanupAssociations();
        $store->reset();
    }

}

?>
