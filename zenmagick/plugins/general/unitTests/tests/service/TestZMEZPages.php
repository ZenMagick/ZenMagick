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
