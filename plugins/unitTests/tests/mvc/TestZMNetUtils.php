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
 * Test ZMNetUtils manager.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMNetUtils extends ZMTestCase {

    /**
     * Test global.
     */
    public function testGetDomain() {
        $this->assertEqual('google.com', ZMNetUtils::getDomain('http://www.google.com/test.html'));
        $this->assertEqual('google.co.uk', ZMNetUtils::getDomain('https://news.google.co.uk/?id=12345'));
        $this->assertEqual('google.com', ZMNetUtils::getDomain('http://my.subdomain.google.com/directory1/page.php?id=abc'));
        $this->assertEqual('google.co.uk', ZMNetUtils::getDomain('https://testing.multiple.subdomain.google.co.uk/'));
        $this->assertEqual('nothingelsethan.com', ZMNetUtils::getDomain('http://nothingelsethan.com'));
        $this->assertEqual('localhost', ZMNetUtils::getDomain('http://localhost/zenmagick/index.php'));
    }

}
