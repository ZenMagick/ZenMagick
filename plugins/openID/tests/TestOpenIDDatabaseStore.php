<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\plugins\openID\tests;

use ZenMagick\plugins\openID\OpenIDDatabaseStore;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test OpenID store implementation.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestOpenIDDatabaseStore extends TestCase {

    /**
     * Test store.
     */
    public function testStore() {
        $store = new OpenIDDatabaseStore();
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
