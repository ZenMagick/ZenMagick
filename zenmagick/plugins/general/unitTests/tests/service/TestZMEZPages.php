<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Test ezpages service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMEZPages extends ZMTestCase {

    /**
     * Test load.
     */
    public function testLoad() {
        $page = ZMEZPages::instance()->getPageForId(8);
        if ($this->assertNotNull($page)) {
            $this->assertEqual(8, $page->getId());
            $this->assertTrue($page->isSidebox());
            $this->assertEqual(40, $page->getSideboxSort());
        }
    }

    /**
     * Test update.
     */
    public function testUpdate() {
        $page = ZMEZPages::instance()->getPageForId(8);
        if ($this->assertNotNull($page)) {
            $page->setHeaderSort(33);
            $status = ZMEZPages::instance()->updatePage($page);
            $this->assertTrue($status);
            $this->assertEqual(33, $page->getHeaderSort());

            // load from scratch
            $page = ZMEZPages::instance()->getPageForId(8);
            if ($this->assertNotNull($page)) {
                $this->assertEqual(33, $page->getHeaderSort());
            }

            // revert change
            $page->setHeaderSort(0);
            ZMEZPages::instance()->updatePage($page);
        }
    }

    /**
     * Test create.
     */
    public function testCreate() {
        // make copy
        $page = ZMEZPages::instance()->getPageForId(8);
        $newPage = ZMEZPages::instance()->createPage($page);
        if ($this->assertNotNull($newPage)) {
            $this->assertNotEqual(8, $newPage->getId());
            ZMEZPages::instance()->removePage($newPage);
        }
    }

}

?>
